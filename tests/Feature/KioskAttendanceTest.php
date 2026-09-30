<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceRule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KioskAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected SchoolClass $class;
    protected Student $student;
    protected AttendanceRule $rule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->class = SchoolClass::create([
            'name' => 'X-RPL 1',
            'level' => 'X',
        ]);

        $this->student = Student::create([
            'nis' => '2026001',
            'name' => 'Muhammad Fauzan',
            'gender' => 'L',
            'class_id' => $this->class->id,
            'barcode_token' => 'STU-2609-FAUZAN',
            'face_descriptor' => json_encode(array_fill(0, 128, 0.5)),
            'parent_name' => 'Ahmad Subagyo',
            'parent_phone' => '6281234567890',
            'is_active' => true,
        ]);

        // Aturan jam: 06:00 masuk, 07:15 deadline tepat waktu, 07:30 batas terlambat, 15:00 - 17:00 pulang
        $this->rule = AttendanceRule::create([
            'name' => 'Reguler Sekolah',
            'time_in_start' => '06:00:00',
            'time_in_deadline' => '07:15:00',
            'time_in_late_limit' => '07:30:00',
            'time_out_start' => '15:00:00',
            'time_out_end' => '17:00:00',
            'active_days' => [1, 2, 3, 4, 5, 6, 7],
            'is_active' => true,
        ]);

        Carbon::setTestNow(Carbon::create(2026, 9, 28, 6, 45, 0, 'Asia/Jakarta'));
    }

    public function test_kiosk_page_accessible(): void
    {
        $response = $this->get(route('kiosk.index'));
        $response->assertStatus(200);
        $response->assertSee('TERMINAL PRESENSI MANDIRI');
    }

    public function test_scan_barcode_masuk_tepat_waktu(): void
    {
        $service = app(AttendanceService::class);
        $mockTime = Carbon::create(2026, 9, 28, 6, 45, 0, 'Asia/Jakarta');

        $result = $service->recordAttendance($this->student, 'barcode', null, $mockTime);

        $this->assertTrue($result['success']);
        $this->assertEquals('masuk', $result['action']);
        $this->assertEquals('hadir', $result['status']);

        $attendance = Attendance::where('student_id', $this->student->id)
            ->whereDate('attendance_date', '2026-09-28')
            ->first();

        $this->assertNotNull($attendance);
        $this->assertEquals('hadir', $attendance->status_in);
        $this->assertEquals('barcode', $attendance->method_in);
    }

    public function test_scan_barcode_masuk_terlambat(): void
    {
        $service = app(AttendanceService::class);
        $mockTime = Carbon::create(2026, 9, 28, 7, 20, 0, 'Asia/Jakarta'); // Lewat dari deadline 07:15

        $result = $service->recordAttendance($this->student, 'barcode', null, $mockTime);

        $this->assertTrue($result['success']);
        $this->assertEquals('masuk', $result['action']);
        $this->assertEquals('terlambat', $result['status']);

        $attendance = Attendance::where('student_id', $this->student->id)->first();
        $this->assertEquals('terlambat', $attendance->status_in);
    }

    public function test_anti_double_tap_presensi_masuk(): void
    {
        $service = app(AttendanceService::class);
        $mockTime1 = Carbon::create(2026, 9, 28, 6, 50, 0, 'Asia/Jakarta');
        $mockTime2 = Carbon::create(2026, 9, 28, 6, 52, 0, 'Asia/Jakarta');

        // Scan pertama
        $res1 = $service->recordAttendance($this->student, 'barcode', null, $mockTime1);
        $this->assertTrue($res1['success']);

        // Scan kedua (harus ditolak karena sudah masuk)
        $res2 = $service->recordAttendance($this->student, 'barcode', null, $mockTime2);
        $this->assertFalse($res2['success']);
        $this->assertEquals('already_in', $res2['type']);
    }

    public function test_scan_presensi_pulang(): void
    {
        $service = app(AttendanceService::class);
        $mockTimeIn = Carbon::create(2026, 9, 28, 6, 50, 0, 'Asia/Jakarta');
        $mockTimeOut = Carbon::create(2026, 9, 28, 15, 30, 0, 'Asia/Jakarta');

        // Masuk dulu
        $service->recordAttendance($this->student, 'face', null, $mockTimeIn);

        // Pulang
        $resultOut = $service->recordAttendance($this->student, 'face', null, $mockTimeOut);
        $this->assertTrue($resultOut['success']);
        $this->assertEquals('pulang', $resultOut['action']);

        $attendance = Attendance::where('student_id', $this->student->id)->first();
        $this->assertNotNull($attendance->time_out);
        $this->assertEquals('pulang_normal', $attendance->status_out);
        $this->assertEquals('face', $attendance->method_out);
    }

    public function test_scan_barcode_via_api_endpoint(): void
    {
        $response = $this->postJson(route('kiosk.api.scan-barcode'), [
            'barcode_token' => 'STU-2609-FAUZAN',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('attendances', [
            'student_id' => $this->student->id,
            'method_in' => 'barcode',
        ]);
    }

    public function test_admin_attendance_report_with_filters(): void
    {
        // Buat data presensi
        Attendance::create([
            'student_id' => $this->student->id,
            'attendance_date' => now()->toDateString(),
            'time_in' => '06:50:00',
            'status_in' => 'hadir',
            'method_in' => 'barcode',
        ]);

        // Cek halaman admin report
        $response = $this->actingAs($this->admin)->get(route('admin.attendances.index', [
            'status' => 'hadir',
            'method' => 'barcode',
            'class_id' => $this->class->id,
        ]));

        $response->assertOk();
        $response->assertSee('Muhammad Fauzan');
        $response->assertSee('X-RPL 1');
    }

    public function test_kiosk_rejects_barcode_outside_operational_hours(): void
    {
        // Set waktu di luar jam operasional (jam 21:00 malam)
        Carbon::setTestNow(Carbon::create(2026, 9, 28, 21, 0, 0, 'Asia/Jakarta'));

        $response = $this->postJson(route('kiosk.api.scan-barcode'), [
            'barcode_token' => 'STU-2609-FAUZAN',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'success' => false,
            'is_closed' => true,
            'type' => 'outside_operational_hours',
        ]);
    }

    public function test_kiosk_allows_barcode_with_bypass_hours_flag(): void
    {
        // Set waktu di luar jam operasional (jam 21:00 malam)
        Carbon::setTestNow(Carbon::create(2026, 9, 28, 21, 0, 0, 'Asia/Jakarta'));

        $response = $this->postJson(route('kiosk.api.scan-barcode'), [
            'barcode_token' => 'STU-2609-FAUZAN',
            'bypass_hours' => true,
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'success' => true,
            'action' => 'masuk',
        ]);
    }

    public function test_kiosk_rejects_face_outside_operational_hours(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 28, 21, 0, 0, 'Asia/Jakarta'));

        $response = $this->postJson(route('kiosk.api.scan-face'), [
            'student_id' => $this->student->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'success' => false,
            'is_closed' => true,
            'type' => 'outside_operational_hours',
        ]);
    }

    public function test_kiosk_allows_face_with_bypass_hours_flag(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 28, 21, 0, 0, 'Asia/Jakarta'));

        $response = $this->postJson(route('kiosk.api.scan-face'), [
            'student_id' => $this->student->id,
            'bypass_hours' => true,
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'success' => true,
            'action' => 'masuk',
        ]);
    }
}
