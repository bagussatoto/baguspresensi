<?php

declare(strict_types=1);

namespace Modules\Penjualan\Domain\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Penjualan\Domain\Contexts\PosShiftContext;
use Modules\Penjualan\Domain\Enums\ShiftStatus;
use Modules\Penjualan\Domain\Models\PosShift;

class PosShiftService
{
    /**
     * Membuka sesi kasir baru (Shift Open) dengan saldo modal laci kas.
     */
    public function openShift(PosShiftContext $context): PosShift
    {
        return DB::transaction(function () use ($context) {
            // Pastikan tidak ada shift aktif yang masih menggantung untuk kasir atau akun laci ini
            $activeShift = PosShift::query()
                ->where('status', ShiftStatus::Open)
                ->where(function ($q) use ($context) {
                    $q->where('cashier_id', $context->cashierId)
                        ->orWhere('kas_account_id', $context->kasAccountId);
                })
                ->first();

            if ($activeShift instanceof PosShift) {
                throw new InvalidArgumentException(
                    "Terdapat shift aktif #{$activeShift->shift_number} yang belum ditutup. Tutup shift sebelumnya terlebih dahulu."
                );
            }

            $date = Carbon::now();
            $shiftNumber = $this->generateShiftNumber($context->branchId, $date);

            return PosShift::create([
                'branch_id' => $context->branchId,
                'kas_account_id' => $context->kasAccountId,
                'cashier_id' => $context->cashierId,
                'shift_number' => $shiftNumber,
                'status' => ShiftStatus::Open,
                'opened_at' => $date,
                'opening_cash' => $context->openingCash,
                'expected_cash' => $context->openingCash,
                'actual_cash' => null,
                'cash_difference' => null,
                'total_sales' => 0.0,
                'total_cash_sales' => 0.0,
                'total_non_cash_sales' => 0.0,
                'notes' => $context->notes,
            ]);
        });
    }

    /**
     * Menutup sesi kasir dengan perhitungan fisik kas (Blind Cash Count).
     */
    public function closeShift(int $shiftId, float $actualCash, ?string $notes = null): PosShift
    {
        return DB::transaction(function () use ($shiftId, $actualCash, $notes) {
            /** @var PosShift $shift */
            $shift = PosShift::query()
                ->where('id', $shiftId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $shift->isOpen()) {
                throw new InvalidArgumentException("Shift #{$shift->shift_number} sudah berstatus {$shift->status->label()}.");
            }

            $diff = $actualCash - $shift->expected_cash;

            $shift->actual_cash = $actualCash;
            $shift->cash_difference = $diff;
            $shift->status = ShiftStatus::Closed;
            $shift->closed_at = Carbon::now();
            if ($notes) {
                $shift->notes = ($shift->notes ? $shift->notes."\n" : '').'Catatan Tutup Shift: '.$notes;
            }
            $shift->save();

            return $shift->fresh(['branch', 'kasAccount', 'cashier']);
        });
    }

    /**
     * Rekonsiliasi audit selisih kasir oleh supervisor / manajer.
     */
    public function reconcileShift(int $shiftId, ?string $notes = null): PosShift
    {
        return DB::transaction(function () use ($shiftId, $notes) {
            /** @var PosShift $shift */
            $shift = PosShift::query()->lockForUpdate()->findOrFail($shiftId);

            $shift->status = ShiftStatus::Reconciled;
            if ($notes) {
                $shift->notes = ($shift->notes ? $shift->notes."\n" : '').'Rekonsiliasi: '.$notes;
            }
            $shift->save();

            return $shift->fresh();
        });
    }

    private function generateShiftNumber(int $branchId, Carbon $date): string
    {
        $prefix = sprintf('SHIFT-%03d-%s-', $branchId, $date->format('Ymd'));
        $count = PosShift::where('shift_number', 'LIKE', "{$prefix}%")->count();

        return sprintf('%s%04d', $prefix, $count + 1);
    }
}
