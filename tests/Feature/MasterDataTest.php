<?php

namespace Tests\Feature;

use App\Models\AttendanceRule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $guru;
    protected SchoolClass $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->guru = User::create([
            'name' => 'Guru Test',
            'email' => 'guru@test.com',
            'password' => bcrypt('password'),
            'role' => 'guru',
        ]);

        $this->class = SchoolClass::create([
            'name' => 'X-RPL 1',
            'level' => 'X',
            'teacher_id' => $this->guru->id,
        ]);
    }

    /**
     * Test login redirect based on role.
     */
    public function test_login_redirects_to_correct_dashboard_based_on_role(): void
    {
        // Admin Login
        $responseAdmin = $this->post(route('login.post'), [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);
        $responseAdmin->assertRedirect(route('admin.dashboard'));

        $this->post(route('logout'));

        // Guru Login
        $responseGuru = $this->post(route('login.post'), [
            'email' => 'guru@test.com',
            'password' => 'password',
        ]);
        $responseGuru->assertRedirect(route('guru.dashboard'));
    }

    /**
     * Test role protection: Guru cannot access admin students route.
     */
    public function test_guru_forbidden_from_admin_routes(): void
    {
        $response = $this->actingAs($this->guru)->get(route('admin.students.index'));
        $response->assertStatus(403);
    }

    /**
     * Test admin can create student and auto-generates barcode token & 62 phone format.
     */
    public function test_admin_can_create_student_with_barcode_token_and_phone_formatted(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.students.store'), [
            'nis' => '2026101',
            'nisn' => '0089999999',
            'name' => 'Siswa Baru Cerdas',
            'gender' => 'L',
            'class_id' => $this->class->id,
            'parent_name' => 'Bapak Siswa',
            'parent_phone' => '085712345678', // Format lokal 08...
        ]);

        $response->assertRedirect(route('admin.students.index'));

        $student = Student::where('nis', '2026101')->first();
        $this->assertNotNull($student);
        $this->assertEquals('Siswa Baru Cerdas', $student->name);
        $this->assertEquals('6285712345678', $student->parent_phone); // Konversi otomatis 628...
        $this->assertStringStartsWith('STU-', $student->barcode_token); // Otomatis barcode unik
    }

    /**
     * Test admin can save face descriptor biometrics via API.
     */
    public function test_admin_can_save_student_face_descriptor(): void
    {
        $student = Student::create([
            'nis' => '2026102',
            'name' => 'Siswa Face Test',
            'gender' => 'P',
            'class_id' => $this->class->id,
            'barcode_token' => 'STU-2609-TEST1',
            'parent_name' => 'Ibu Face',
            'parent_phone' => '628123456789',
        ]);

        $mockDescriptor = json_encode(array_fill(0, 128, 0.12345));

        $response = $this->actingAs($this->admin)->postJson(route('admin.students.save-face', $student), [
            'face_descriptor' => $mockDescriptor,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $student->refresh();
        $this->assertEquals($mockDescriptor, $student->face_descriptor);
    }

    /**
     * Test admin can create attendance rules with timing and active days array.
     */
    public function test_admin_can_create_attendance_rule(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.rules.store'), [
            'name' => 'Jadwal Ujian Khusus',
            'time_in_start' => '07:00',
            'time_in_deadline' => '07:30',
            'time_in_late_limit' => '08:00',
            'time_out_start' => '12:00',
            'time_out_end' => '14:00',
            'active_days' => [1, 2, 3],
        ]);

        $response->assertRedirect(route('admin.rules.index'));

        $rule = AttendanceRule::where('name', 'Jadwal Ujian Khusus')->first();
        $this->assertNotNull($rule);
        $this->assertEquals([1, 2, 3], $rule->active_days);
    }
}
