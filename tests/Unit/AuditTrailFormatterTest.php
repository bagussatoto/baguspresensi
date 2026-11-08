<?php

namespace Tests\Unit;

use App\Domains\Attendance\Models\AuditLog;
use App\Domains\Attendance\Services\AuditTrailFormatter;
use PHPUnit\Framework\TestCase;

class AuditTrailFormatterTest extends TestCase
{
    public function test_formats_payload_in_indonesian_correctly(): void
    {
        $payload = [
            'branch_name' => 'Kopi Bagus Thamrin',
            'grand_total' => 75000,
            'is_sales_locked' => true,
            'status' => 'paid',
            'created_at' => '2026-11-08T10:00:00Z',
        ];

        $formatted = AuditTrailFormatter::formatPayload($payload, 'id');

        $this->assertCount(5, $formatted);

        $branchItem = collect($formatted)->firstWhere('key', 'branch_name');
        $this->assertEquals('Nama Cabang', $branchItem['label']);
        $this->assertEquals('Kopi Bagus Thamrin', $branchItem['value']);

        $totalItem = collect($formatted)->firstWhere('key', 'grand_total');
        $this->assertEquals('Total Akhir / Tagihan', $totalItem['label']);
        $this->assertStringContainsString('Rp 75.000', $totalItem['value']);

        $lockedItem = collect($formatted)->firstWhere('key', 'is_sales_locked');
        $this->assertEquals('Status Kunci Penjualan', $lockedItem['label']);
        $this->assertEquals('Aktif / Ya', $lockedItem['value']);

        $statusItem = collect($formatted)->firstWhere('key', 'status');
        $this->assertEquals('Status', $statusItem['label']);
        $this->assertEquals('Lunas (Paid)', $statusItem['value']);
    }

    public function test_formats_payload_in_english_correctly(): void
    {
        $payload = [
            'branch_name' => 'Kopi Bagus Thamrin',
            'paid_amount' => 50000,
            'is_locked' => false,
            'payment_status' => 'pending',
        ];

        $formatted = AuditTrailFormatter::formatPayload($payload, 'en');

        $this->assertCount(4, $formatted);

        $branchItem = collect($formatted)->firstWhere('key', 'branch_name');
        $this->assertEquals('Branch Name', $branchItem['label']);

        $paidItem = collect($formatted)->firstWhere('key', 'paid_amount');
        $this->assertEquals('Paid Amount', $paidItem['label']);
        $this->assertStringContainsString('Rp 50.000', $paidItem['value']);

        $lockedItem = collect($formatted)->firstWhere('key', 'is_locked');
        $this->assertEquals('Lock Status', $lockedItem['label']);
        $this->assertEquals('Inactive / No', $lockedItem['value']);

        $statusItem = collect($formatted)->firstWhere('key', 'payment_status');
        $this->assertEquals('Payment Status', $statusItem['label']);
        $this->assertEquals('Pending Approval', $statusItem['value']);
    }

    public function test_audit_log_model_returns_formatted_human_readable_array(): void
    {
        $auditLog = new AuditLog;
        $auditLog->old_values = [
            'stock_after' => 10,
            'cash_balance' => 100000,
        ];
        $auditLog->new_values = [
            'stock_after' => 15,
            'cash_balance' => 150000,
        ];

        $oldId = $auditLog->formattedOldValues('id');
        $newId = $auditLog->formattedNewValues('id');

        $this->assertEquals('Stok Akhir', $oldId[0]['label']);
        $this->assertEquals('Saldo Kas Fisik', $oldId[1]['label']);
        $this->assertStringContainsString('Rp 100.000', $oldId[1]['value']);

        $this->assertEquals('Stok Akhir', $newId[0]['label']);
        $this->assertStringContainsString('Rp 150.000', $newId[1]['value']);
    }
}
