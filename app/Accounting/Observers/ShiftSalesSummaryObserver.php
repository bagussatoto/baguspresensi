<?php

declare(strict_types=1);

namespace App\Accounting\Observers;

use App\Accounting\Services\AppAutoJournalService;
use App\Models\ShiftSalesSummary;
use Modules\Kas\Domain\Services\AutoJournalService;

class ShiftSalesSummaryObserver
{
    /**
     * Tangani event 'saved' pada ShiftSalesSummary.
     * Memicu penjurnalan otomatis saat shift ditutup dengan selisih kas,
     * atau saat admin menyelesaikan rekonsiliasi selisih kas.
     */
    public function saved(ShiftSalesSummary $summary): void
    {
        if ($summary->status === 'closed' && abs((float) $summary->cash_difference) > 0.01) {
            rescue(function () use ($summary) {
                /** @var AppAutoJournalService $journalService */
                $journalService = app(AutoJournalService::class);
                if (method_exists($journalService, 'journalCashDiscrepancy')) {
                    $journalService->journalCashDiscrepancy($summary);
                }
            });
        }
    }
}
