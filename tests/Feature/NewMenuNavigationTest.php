<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewMenuNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $guru;
    protected SchoolClass $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Utama',
            'email' => 'admin@smkn77.sch.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->guru = User::create([
            'name' => 'Budi Santoso, S.Pd',
            'email' => 'budi@smkn77.sch.id',
            'password' => bcrypt('password'),
            'role' => 'guru',
        ]);

        $this->class = SchoolClass::create([
            'name' => 'XII RPL 1',
            'level' => 'XII',
            'teacher_id' => $this->guru->id,
        ]);

        Student::create([
            'nis' => '10293847',
            'nisn' => '0081234567',
            'name' => 'Ahmad Fauzi',
            'class_id' => $this->class->id,
            'gender' => 'L',
            'barcode_token' => 'STU-10293847',
            'parent_name' => 'Bapak Fauzi',
            'parent_phone' => '6281234567891',
        ]);
    }

    /**
     * Test all 9 sidebar menu routes return 200 OK for admin.
     */
    public function test_all_sidebar_menu_pages_accessible_by_admin(): void
    {
        $this->actingAs($this->admin);

        // 1. Dashboard
        $response = $this->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Dashboard');

        // 2. Presensi Siswa
        $response = $this->get(route('admin.attendances.index'));
        $response->assertOk();
        $response->assertSee('Presensi Siswa');

        // 3. Data Siswa
        $response = $this->get(route('admin.students.index'));
        $response->assertOk();
        $response->assertSee('Data Siswa');

        // 4. Data Guru
        $response = $this->get(route('admin.teachers.index'));
        $response->assertOk();
        $response->assertSee('Data Guru');

        // 5. Data Kelas & Jurusan
        $response = $this->get(route('admin.classes.index'));
        $response->assertOk();
        $response->assertSee('Data Kelas & Jurusan');

        // 6. Generate QR Code
        $response = $this->get(route('admin.qrcode.index'));
        $response->assertOk();
        $response->assertSee('Generate QR Code');

        // 7. Generate Laporan
        $response = $this->get(route('admin.reports.index'));
        $response->assertOk();
        $response->assertSee('Generate Laporan');

        // 8. Data Petugas
        $response = $this->get(route('admin.officers.index'));
        $response->assertOk();
        $response->assertSee('Data Petugas');

        // 9. Pengaturan
        $response = $this->get(route('admin.settings.index'));
        $response->assertOk();
        $response->assertSee('Pengaturan');
    }

    /**
     * Test teacher CRUD functionality.
     */
    public function test_teacher_can_be_created_and_assigned_class(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.teachers.store'), [
            'name' => 'Siti Nurhaliza, M.Kom',
            'email' => 'siti@smkn77.sch.id',
            'password' => 'secret123',
            'class_id' => $this->class->id,
        ]);

        $response->assertRedirect(route('admin.teachers.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'siti@smkn77.sch.id',
            'role' => 'guru',
        ]);
    }

    /**
     * Test batch print QR Code page.
     */
    public function test_batch_print_qr_code_page_works(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.qrcode.batch-print'), [
            'class_id' => $this->class->id,
        ]);

        $response->assertOk();
        $response->assertSee('SMK NEGERI 77 JAKARTA');
        $response->assertSee('10293847');
    }
}
