<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceRule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SelfAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $guru;
    protected SchoolClass $class;
    protected Student $student;
    protected AttendanceRule $rule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Sekolah',
            'email' => 'admin@sekolah.sch.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->guru = User::create([
            'name' => 'Budi Santoso, S.Pd',
            'email' => 'budi@sekolah.sch.id',
            'password' => bcrypt('password'),
            'role' => 'guru',
        ]);

        $this->class = SchoolClass::create([
            'name' => 'XI-RPL 1',
            'level' => 'XI',
            'teacher_id' => $this->guru->id,
        ]);

        $this->student = Student::create([
            'nis' => '2026101',
            'name' => 'Rizky Pratama',
            'gender' => 'L',
            'class_id' => $this->class->id,
            'barcode_token' => 'STU-2609-RIZKY',
            'parent_name' => 'Hendro',
            'parent_phone' => '6281122334455',
            'is_active' => true,
        ]);

        // Aturan jam: 06:00 buka masuk, 07:00 deadline tepat waktu, 07:30 batas maksimal telat, 15:00 - 17:00 pulang
        $this->rule = AttendanceRule::create([
            'name' => 'Jadwal Reguler',
            'time_in_start' => '06:00:00',
            'time_in_deadline' => '07:00:00',
            'time_in_late_limit' => '07:30:00',
            'time_out_start' => '15:00:00',
            'time_out_end' => '17:00:00',
            'active_days' => [1, 2, 3, 4, 5, 6, 7],
            'is_active' => true,
        ]);

        Carbon::setTestNow(Carbon::create(2026, 9, 30, 6, 45, 0, 'Asia/Jakarta'));
    }

    public function test_self_attendance_page_is_accessible(): void
    {
        $response = $this->get(route('presensi.index'));
        $response->assertOk();
        $response->assertSee('Presensi Mandiri');
        $response->assertSee('Wi-Fi Sekolah');
    }

    public function test_wifi_restriction_blocks_unauthorized_ip(): void
    {
        config([
            'attendance.enforce_wifi_restriction' => true,
            'attendance.school_wifi_subnets' => ['192.168.10.0/24'],
        ]);

        // Request dari IP luar
        $response = $this->withServerVariables(['REMOTE_ADDR' => '180.252.1.20'])
            ->get(route('presensi.index'));
        $response->assertStatus(403);
        $response->assertSee('Wi-Fi Sekolah Diperlukan');

        // Request API dari IP luar
        $apiResponse = $this->withServerVariables(['REMOTE_ADDR' => '180.252.1.20'])
            ->postJson(route('presensi.api.status'), ['nis' => '2026101']);
        $apiResponse->assertStatus(403);
        $apiResponse->assertJsonFragment(['error_code' => 'OUTSIDE_SCHOOL_NETWORK']);

        // Request dari IP subnet Wi-Fi sekolah yang diizinkan
        $validResponse = $this->withServerVariables(['REMOTE_ADDR' => '192.168.10.45'])
            ->get(route('presensi.index'));
        $validResponse->assertOk();
    }

    public function test_device_lock_first_time_locks_device(): void
    {
        $this->assertFalse($this->student->isDeviceLocked());

        $response = $this->postJson(route('presensi.api.submit'), [
            'nis' => '2026101',
            'device_id' => 'DEV-UUID-TEST-001',
            'device_name' => 'Samsung Galaxy A54',
        ]);

        $response->assertOk();
        $this->student->refresh();

        $this->assertTrue($this->student->isDeviceLocked());
        $this->assertEquals('DEV-UUID-TEST-001', $this->student->device_id);
        $this->assertEquals('Samsung Galaxy A54', $this->student->device_name);
        $this->assertNotNull($this->student->device_locked_at);
    }

    public function test_device_lock_rejects_different_device(): void
    {
        // Kunci ke HP 1
        $this->student->lockToDevice('DEV-UUID-HP-1', 'Xiaomi Redmi Note');

        // Coba absen pakai HP 2 (titip absen ke teman)
        $response = $this->postJson(route('presensi.api.submit'), [
            'nis' => '2026101',
            'device_id' => 'DEV-UUID-HP-2',
            'device_name' => 'iPhone 13',
        ]);

        $response->assertStatus(403);
        $response->assertJsonFragment(['error_code' => 'DEVICE_MISMATCH']);
    }

    public function test_admin_can_reset_device_lock(): void
    {
        $this->student->lockToDevice('DEV-UUID-HP-1', 'HP Lama Rusak');
        $this->assertTrue($this->student->fresh()->isDeviceLocked());

        $response = $this->actingAs($this->admin)->post(route('admin.students.reset-device', $this->student));
        $response->assertSessionHas('success');

        $this->assertFalse($this->student->fresh()->isDeviceLocked());
        $this->assertNull($this->student->fresh()->device_id);
    }

    public function test_operational_hours_rejects_early_attendance(): void
    {
        // Set waktu jam 05:15 (sebelum 06:00)
        Carbon::setTestNow(Carbon::create(2026, 9, 30, 5, 15, 0, 'Asia/Jakarta'));

        $response = $this->postJson(route('presensi.api.submit'), [
            'nis' => '2026101',
            'device_id' => 'DEV-TEST-HP',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['type' => 'early_in']);
    }

    public function test_operational_hours_rejects_after_late_limit(): void
    {
        // Set waktu jam 08:00 (lewat batas late_limit 07:30 dan sebelum jam pulang 15:00)
        Carbon::setTestNow(Carbon::create(2026, 9, 30, 8, 0, 0, 'Asia/Jakarta'));

        $response = $this->postJson(route('presensi.api.submit'), [
            'nis' => '2026101',
            'device_id' => 'DEV-TEST-HP',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['type' => 'late_limit_exceeded']);
    }

    public function test_self_attendance_flow_masuk_and_pulang(): void
    {
        // 1. Presensi Masuk (Jam 06:45 - Tepat Waktu)
        Carbon::setTestNow(Carbon::create(2026, 9, 30, 6, 45, 0, 'Asia/Jakarta'));

        $responseIn = $this->postJson(route('presensi.api.submit'), [
            'nis' => '2026101',
            'device_id' => 'DEV-HP-RIZKY',
            'device_name' => 'HP Rizky',
        ]);

        $responseIn->assertOk();
        $responseIn->assertJsonFragment(['action' => 'masuk', 'status' => 'hadir']);

        $attendance = Attendance::where('student_id', $this->student->id)
            ->whereDate('attendance_date', '2026-09-30')
            ->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('self_wifi', $attendance->method_in);
        $this->assertEquals('hadir', $attendance->status_in);

        // 2. Coba dobel presensi masuk pada jam 06:50 -> harus ditolak
        Carbon::setTestNow(Carbon::create(2026, 9, 30, 6, 50, 0, 'Asia/Jakarta'));
        $responseDouble = $this->postJson(route('presensi.api.submit'), [
            'nis' => '2026101',
            'device_id' => 'DEV-HP-RIZKY',
        ]);
        $responseDouble->assertStatus(422);
        $responseDouble->assertJsonFragment(['type' => 'already_in']);

        // 3. Presensi Pulang (Jam 15:30)
        Carbon::setTestNow(Carbon::create(2026, 9, 30, 15, 30, 0, 'Asia/Jakarta'));
        $responseOut = $this->postJson(route('presensi.api.submit'), [
            'nis' => '2026101',
            'device_id' => 'DEV-HP-RIZKY',
        ]);

        $responseOut->assertOk();
        $responseOut->assertJsonFragment(['action' => 'pulang', 'status' => 'pulang_normal']);

        $attendance->refresh();
        $this->assertNotNull($attendance->time_out);
        $this->assertEquals('self_wifi', $attendance->method_out);
        $this->assertEquals('pulang_normal', $attendance->status_out);
    }
}
