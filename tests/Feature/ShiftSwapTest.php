<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Attendance\Enums\ShiftSwapStatus;
use App\Domains\Attendance\Models\ShiftSwapRequest;
use App\Domains\Employee\Models\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Employee;
use Tests\TestCase;

class ShiftSwapTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branchPusat;

    private Branch $branchCabang;

    private Employee $requester;

    private Employee $colleague;

    private Employee $differentBranchColleague;

    private Employee $supervisor;

    private Shift $shift1;

    private Shift $shift2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branchPusat = Branch::create([
            'name' => 'MGK Net Pusat',
            'code' => 'NET-PST',
            'latitude' => -6.15850000,
            'longitude' => 106.90800000,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $this->branchCabang = Branch::create([
            'name' => 'MGK Net Cabang Barat',
            'code' => 'NET-BRT',
            'latitude' => -6.16000000,
            'longitude' => 106.90000000,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $this->requester = Employee::create([
            'branch_id' => $this->branchPusat->id,
            'name' => 'Operator Hendra',
            'code' => 'OP-001',
            'username' => 'hendra',
            'is_active' => true,
        ]);

        $this->colleague = Employee::create([
            'branch_id' => $this->branchPusat->id,
            'name' => 'Operator Dimas',
            'code' => 'OP-002',
            'username' => 'dimas',
            'is_active' => true,
        ]);

        $this->differentBranchColleague = Employee::create([
            'branch_id' => $this->branchCabang->id,
            'name' => 'Operator Rian',
            'code' => 'OP-003',
            'username' => 'rian',
            'is_active' => true,
        ]);

        $this->supervisor = Employee::create([
            'branch_id' => $this->branchPusat->id,
            'name' => 'Supervisor Rudi',
            'code' => 'SPV-001',
            'username' => 'spv_rudi',
            'is_active' => true,
        ]);

        $this->shift1 = Shift::create([
            'name' => 'Shift Pagi (Shift 2)',
            'code' => 'SHIFT-2',
            'start_time' => '07:00:00',
            'end_time' => '13:00:00',
            'is_overnight' => false,
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);

        $this->shift2 = Shift::create([
            'name' => 'Shift Siang (Shift 3)',
            'code' => 'SHIFT-3',
            'start_time' => '13:00:00',
            'end_time' => '19:00:00',
            'is_overnight' => false,
            'grace_period_minutes' => 15,
            'is_active' => true,
        ]);
    }

    public function test_unauthenticated_employee_cannot_access_shift_swap_page(): void
    {
        $response = $this->get(route('presensi.tukar-shift'));
        $response->assertRedirect(route('presensi.login'));
    }

    public function test_authenticated_employee_can_view_shift_swap_page(): void
    {
        $response = $this->withSession(['presensi_employee_id' => $this->requester->id])
            ->get(route('presensi.tukar-shift'));

        $response->assertOk()
            ->assertViewIs('presensi.tukar-shift')
            ->assertSee('Ajukan Tukar Shift')
            ->assertSee('Operator Dimas')
            ->assertDontSee('Operator Rian'); // Cabang berbeda tidak muncul di dropdown
    }

    public function test_employee_can_submit_shift_swap_request_to_same_branch_colleague(): void
    {
        $today = Carbon::today()->toDateString();

        $response = $this->withSession(['presensi_employee_id' => $this->requester->id])
            ->post(route('presensi.tukar-shift.submit'), [
                'target_employee_id' => $this->colleague->id,
                'requester_shift_id' => $this->shift1->id,
                'requester_date' => $today,
                'target_shift_id' => $this->shift2->id,
                'target_date' => $today,
                'reason' => 'Ada acara wisuda adik pagi hari, ingin tukar ke shift siang',
            ]);

        $response->assertRedirect(route('presensi.tukar-shift'))
            ->assertSessionHas('success');

        $swap = ShiftSwapRequest::where('requester_id', $this->requester->id)
            ->where('target_employee_id', $this->colleague->id)
            ->first();

        $this->assertNotNull($swap);
        $this->assertEquals(ShiftSwapStatus::PendingPeer, $swap->status);
        $this->assertEquals($this->branchPusat->id, $swap->branch_id);
        $this->assertEquals('Ada acara wisuda adik pagi hari, ingin tukar ke shift siang', $swap->reason);
    }

    public function test_employee_cannot_swap_with_different_branch_colleague(): void
    {
        $today = Carbon::today()->toDateString();

        $response = $this->withSession(['presensi_employee_id' => $this->requester->id])
            ->post(route('presensi.tukar-shift.submit'), [
                'target_employee_id' => $this->differentBranchColleague->id,
                'requester_shift_id' => $this->shift1->id,
                'requester_date' => $today,
                'target_shift_id' => $this->shift2->id,
                'target_date' => $today,
                'reason' => 'Tukar beda cabang',
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('shift_swap_requests', [
            'requester_id' => $this->requester->id,
            'target_employee_id' => $this->differentBranchColleague->id,
        ]);
    }

    public function test_colleague_can_agree_to_swap_and_status_becomes_pending_supervisor(): void
    {
        $today = Carbon::today()->toDateString();

        $swap = ShiftSwapRequest::create([
            'branch_id' => $this->branchPusat->id,
            'requester_id' => $this->requester->id,
            'target_employee_id' => $this->colleague->id,
            'requester_shift_id' => $this->shift1->id,
            'requester_date' => $today,
            'target_shift_id' => $this->shift2->id,
            'target_date' => $today,
            'reason' => 'Keperluan mendesak',
            'status' => ShiftSwapStatus::PendingPeer,
        ]);

        $response = $this->withSession(['presensi_employee_id' => $this->colleague->id])
            ->post(route('presensi.tukar-shift.peer-respond', $swap->id), [
                'agree' => 1,
                'note' => 'Siap mas, saya bisa isi shift pagi.',
            ]);

        $response->assertRedirect(route('presensi.tukar-shift'))
            ->assertSessionHas('success');

        $swap->refresh();
        $this->assertEquals(ShiftSwapStatus::PendingSupervisor, $swap->status);
        $this->assertNotNull($swap->peer_responded_at);
        $this->assertEquals('Siap mas, saya bisa isi shift pagi.', $swap->peer_note);
    }

    public function test_colleague_can_reject_swap_with_note(): void
    {
        $today = Carbon::today()->toDateString();

        $swap = ShiftSwapRequest::create([
            'branch_id' => $this->branchPusat->id,
            'requester_id' => $this->requester->id,
            'target_employee_id' => $this->colleague->id,
            'requester_shift_id' => $this->shift1->id,
            'requester_date' => $today,
            'target_shift_id' => $this->shift2->id,
            'target_date' => $today,
            'reason' => 'Keperluan mendesak',
            'status' => ShiftSwapStatus::PendingPeer,
        ]);

        $response = $this->withSession(['presensi_employee_id' => $this->colleague->id])
            ->post(route('presensi.tukar-shift.peer-respond', $swap->id), [
                'agree' => 0,
                'note' => 'Maaf, saya ada jadwal kuliah.',
            ]);

        $response->assertRedirect(route('presensi.tukar-shift'))
            ->assertSessionHas('success');

        $swap->refresh();
        $this->assertEquals(ShiftSwapStatus::PeerRejected, $swap->status);
        $this->assertEquals('Maaf, saya ada jadwal kuliah.', $swap->peer_note);
    }

    public function test_requester_can_cancel_pending_swap(): void
    {
        $today = Carbon::today()->toDateString();

        $swap = ShiftSwapRequest::create([
            'branch_id' => $this->branchPusat->id,
            'requester_id' => $this->requester->id,
            'target_employee_id' => $this->colleague->id,
            'requester_shift_id' => $this->shift1->id,
            'requester_date' => $today,
            'target_shift_id' => $this->shift2->id,
            'target_date' => $today,
            'reason' => 'Salah jadwal',
            'status' => ShiftSwapStatus::PendingPeer,
        ]);

        $response = $this->withSession(['presensi_employee_id' => $this->requester->id])
            ->post(route('presensi.tukar-shift.cancel', $swap->id));

        $response->assertRedirect(route('presensi.tukar-shift'))
            ->assertSessionHas('success');

        $swap->refresh();
        $this->assertEquals(ShiftSwapStatus::Cancelled, $swap->status);
    }

    public function test_supervisor_can_view_and_approve_shift_swap(): void
    {
        $today = Carbon::today()->toDateString();

        $swap = ShiftSwapRequest::create([
            'branch_id' => $this->branchPusat->id,
            'requester_id' => $this->requester->id,
            'target_employee_id' => $this->colleague->id,
            'requester_shift_id' => $this->shift1->id,
            'requester_date' => $today,
            'target_shift_id' => $this->shift2->id,
            'target_date' => $today,
            'reason' => 'Tukar shift dinas',
            'status' => ShiftSwapStatus::PendingSupervisor,
            'peer_responded_at' => Carbon::now(),
            'peer_note' => 'Setuju',
        ]);

        $adminResponse = $this->get(route('admin.presensi.approval'));
        $adminResponse->assertOk()
            ->assertSee('Operator Hendra')
            ->assertSee('Operator Dimas')
            ->assertSee('Tukar shift dinas');

        $approveResponse = $this->post(route('admin.presensi.swap.approve', $swap->id));
        $approveResponse->assertRedirect()
            ->assertSessionHas('success');

        $swap->refresh();
        $this->assertEquals(ShiftSwapStatus::Approved, $swap->status);
        $this->assertNotNull($swap->approved_at);
    }

    public function test_supervisor_can_reject_shift_swap_with_note(): void
    {
        $today = Carbon::today()->toDateString();

        $swap = ShiftSwapRequest::create([
            'branch_id' => $this->branchPusat->id,
            'requester_id' => $this->requester->id,
            'target_employee_id' => $this->colleague->id,
            'requester_shift_id' => $this->shift1->id,
            'requester_date' => $today,
            'target_shift_id' => $this->shift2->id,
            'target_date' => $today,
            'reason' => 'Tukar shift dinas',
            'status' => ShiftSwapStatus::PendingSupervisor,
            'peer_responded_at' => Carbon::now(),
        ]);

        $rejectResponse = $this->post(route('admin.presensi.swap.reject', $swap->id), [
            'rejection_note' => 'Kebutuhan personil shift pagi membutuhkan operator senior.',
        ]);

        $rejectResponse->assertRedirect()
            ->assertSessionHas('success');

        $swap->refresh();
        $this->assertEquals(ShiftSwapStatus::Rejected, $swap->status);
        $this->assertEquals('Kebutuhan personil shift pagi membutuhkan operator senior.', $swap->rejection_note);
    }
}
