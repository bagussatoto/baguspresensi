<?php

namespace Tests\Unit;

use App\Domains\Attendance\Enums\ApprovalStatus;
use App\Domains\Attendance\Enums\AttendanceStatus;
use App\Domains\Attendance\Enums\VerificationMethod;
use App\Domains\Employee\Models\Shift;
use App\Domains\Leave\Enums\LeaveType;
use PHPUnit\Framework\TestCase;

class AttendanceDomainModelTest extends TestCase
{
    public function test_attendance_status_enums_return_expected_labels_and_badges(): void
    {
        $this->assertSame('present', AttendanceStatus::Present->value);
        $this->assertSame('Hadir Tepat Waktu', AttendanceStatus::Present->label());
        $this->assertStringContainsString('emerald', AttendanceStatus::Present->badgeClass());

        $this->assertSame('late', AttendanceStatus::Late->value);
        $this->assertSame('Terlambat', AttendanceStatus::Late->label());
        $this->assertStringContainsString('amber', AttendanceStatus::Late->badgeClass());
    }

    public function test_verification_method_enums(): void
    {
        $this->assertSame('gps_selfie', VerificationMethod::GpsSelfie->value);
        $this->assertSame('GPS + Foto Selfie', VerificationMethod::GpsSelfie->label());
    }

    public function test_approval_status_enums(): void
    {
        $this->assertSame('pending', ApprovalStatus::Pending->value);
        $this->assertSame('Menunggu Persetujuan', ApprovalStatus::Pending->label());
        $this->assertSame('approved', ApprovalStatus::Approved->value);
    }

    public function test_leave_type_enums(): void
    {
        $this->assertSame('annual', LeaveType::Annual->value);
        $this->assertSame('Cuti Tahunan', LeaveType::Annual->label());
        $this->assertSame('sick', LeaveType::Sick->value);
    }

    public function test_shift_model_can_cast_attributes(): void
    {
        $shift = new Shift([
            'code' => 'SHIFT-4',
            'name' => 'Shift 4 (Malam)',
            'start_time' => '19:00:00',
            'end_time' => '01:00:00',
            'is_overnight' => true,
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);

        $this->assertTrue($shift->is_overnight);
        $this->assertSame(15, $shift->grace_period_minutes);
        $this->assertSame('SHIFT-4', $shift->code);
    }
}
