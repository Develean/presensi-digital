<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceRule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Models\WaLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseStructureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test all required database tables exist.
     */
    public function test_all_tables_exist(): void
    {
        $tables = [
            'users',
            'classes',
            'students',
            'attendance_rules',
            'attendances',
            'wa_logs',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Table {$table} does not exist in the database.");
        }
    }

    /**
     * Test users table has role column and user roles work.
     */
    public function test_users_table_structure_and_roles(): void
    {
        $this->assertTrue(Schema::hasColumns('users', ['id', 'name', 'email', 'password', 'role']));

        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $guru = User::create([
            'name' => 'Guru Test',
            'email' => 'guru@test.com',
            'password' => bcrypt('password'),
            'role' => 'guru',
        ]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isGuru());
        $this->assertTrue($guru->isGuru());
        $this->assertFalse($guru->isAdmin());
    }

    /**
     * Test relationship between class, students, and attendances.
     */
    public function test_models_relationships_and_constraints(): void
    {
        $guru = User::create([
            'name' => 'Wali Kelas 1',
            'email' => 'wali@test.com',
            'password' => bcrypt('password'),
            'role' => 'guru',
        ]);

        $class = SchoolClass::create([
            'name' => 'X-RPL 1',
            'level' => 'X',
            'teacher_id' => $guru->id,
        ]);

        $student = Student::create([
            'nis' => '1001',
            'nisn' => '001001',
            'name' => 'Budi Siswa',
            'gender' => 'L',
            'class_id' => $class->id,
            'barcode_token' => 'TOKEN-1001',
            'parent_name' => 'Orang Tua Budi',
            'parent_phone' => '628111111111',
            'is_active' => true,
        ]);

        $this->assertEquals('X-RPL 1', $student->schoolClass->name);
        $this->assertCount(1, $class->students);

        $attendance = Attendance::create([
            'student_id' => $student->id,
            'attendance_date' => now()->toDateString(),
            'time_in' => '06:55:00',
            'status_in' => 'hadir',
            'method_in' => 'face',
        ]);

        $this->assertEquals($student->id, $attendance->student->id);
        $this->assertCount(1, $student->attendances);

        // Test WaLog
        $waLog = WaLog::create([
            'student_id' => $student->id,
            'phone_number' => $student->parent_phone,
            'message_content' => 'Pesan pemberitahuan',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->assertEquals($student->id, $waLog->student->id);
    }

    /**
     * Test attendance rules table structure and active days casting.
     */
    public function test_attendance_rule_casting(): void
    {
        $rule = AttendanceRule::create([
            'name' => 'Jumat Barokah',
            'time_in_start' => '06:00:00',
            'time_in_deadline' => '07:00:00',
            'time_in_late_limit' => '07:15:00',
            'time_out_start' => '11:00:00',
            'time_out_end' => '12:00:00',
            'active_days' => [5],
            'is_active' => true,
        ]);

        $this->assertIsArray($rule->active_days);
        $this->assertContains(5, $rule->active_days);
    }
}
