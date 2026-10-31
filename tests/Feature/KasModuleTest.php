<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Modules\Kas\Domain\Contexts\KasKeluarContext;
use Modules\Kas\Domain\Contexts\KasMasukContext;
use Modules\Kas\Domain\Contexts\KasMutasiContext;
use Modules\Kas\Domain\Enums\KasAccountType;
use Modules\Kas\Domain\Enums\KasTransactionType;
use Modules\Kas\Domain\Enums\PaymentMethodType;
use Modules\Kas\Domain\Exceptions\InsufficientCashBalanceException;
use Modules\Kas\Domain\Models\KasAccount;
use Modules\Kas\Domain\Services\KasKeluarService;
use Modules\Kas\Domain\Services\KasMasukService;
use Modules\Kas\Domain\Services\KasMutasiService;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Customer;
use Tests\TestCase;

class KasModuleTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private KasAccount $kasToko;

    private KasAccount $bankMandiri;

    private KasMasukService $kasMasukService;

    private KasKeluarService $kasKeluarService;

    private KasMutasiService $kasMutasiService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'code' => 'BR-001',
            'name' => 'Cabang Surabaya Pusat',
        ]);

        $this->kasToko = KasAccount::create([
            'branch_id' => $this->branch->id,
            'code' => 'KAS-TOKO-01',
            'name' => 'Kasir Toko Laci 1',
            'type' => KasAccountType::KasirToko,
            'current_balance' => 500_000.00,
        ]);

        $this->bankMandiri = KasAccount::create([
            'branch_id' => $this->branch->id,
            'code' => 'BANK-MANDIRI-01',
            'name' => 'Bank Mandiri Operasional',
            'type' => KasAccountType::Bank,
            'current_balance' => 10_000_000.00,
            'bank_name' => 'Bank Mandiri',
            'account_number' => '1420012345678',
            'account_holder' => 'PT Everest ERP',
        ]);

        $this->kasMasukService = app(KasMasukService::class);
        $this->kasKeluarService = app(KasKeluarService::class);
        $this->kasMutasiService = app(KasMutasiService::class);
    }

    public function test_kas_masuk_increases_balance_and_records_transaction(): void
    {
        $context = new KasMasukContext(
            kasAccountId: $this->kasToko->id,
            branchId: $this->branch->id,
            paymentMethod: PaymentMethodType::Tunai,
            amount: 250_000.00,
            referenceNumber: 'POS-202610-0001',
            description: 'Penjualan retail POS kasir',
        );

        $transaction = $this->kasMasukService->execute($context);

        $this->assertNotNull($transaction->id);
        $this->assertStringStartsWith('KM-', $transaction->transaction_number);
        $this->assertSame(KasTransactionType::Masuk, $transaction->transaction_type);
        $this->assertSame(250_000.00, (float) $transaction->amount);

        // Saldo awal 500.000 + 250.000 = 750.000
        $this->kasToko->refresh();
        $this->assertEquals(750_000.00, $this->kasToko->current_balance);
    }

    public function test_kas_masuk_with_lines_restores_customer_credit_limit(): void
    {
        $customer = Customer::create([
            'code' => 'CUST-001',
            'name' => 'PT Maju Terus',
            'credit_limit' => 5_000_000.00,
            'current_credit_used' => 2_000_000.00,
        ]);

        $context = new KasMasukContext(
            kasAccountId: $this->bankMandiri->id,
            branchId: $this->branch->id,
            paymentMethod: PaymentMethodType::BankTransfer,
            amount: 1_000_000.00,
            customerId: $customer->id,
            referenceNumber: 'INV-202610-001',
            description: 'Pelunasan piutang sebagian invoice INV-001',
            lines: [
                [
                    'reference_type' => 'sales_invoices',
                    'reference_id' => 101,
                    'allocation_amount' => 1_000_000.00,
                    'notes' => 'Pelunasan termin 1',
                ],
            ]
        );

        $transaction = $this->kasMasukService->execute($context);

        $this->assertCount(1, $transaction->lines);
        $this->assertEquals(1_000_000.00, $transaction->lines->first()->allocation_amount);

        // Bank saldo: 10jt + 1jt = 11jt
        $this->bankMandiri->refresh();
        $this->assertEquals(11_000_000.00, $this->bankMandiri->current_balance);

        // Plafon kredit customer pulih: 2jt - 1jt = 1jt
        $customer->refresh();
        $this->assertEquals(1_000_000.00, $customer->current_credit_used);
        $this->assertEquals(4_000_000.00, $customer->remainingCreditLimit());
    }

    public function test_kas_keluar_decreases_balance(): void
    {
        $context = new KasKeluarContext(
            kasAccountId: $this->kasToko->id,
            branchId: $this->branch->id,
            paymentMethod: PaymentMethodType::Tunai,
            amount: 150_000.00,
            description: 'Biaya pembelian air galon dan kertas struk',
        );

        $transaction = $this->kasKeluarService->execute($context);

        $this->assertStringStartsWith('KK-', $transaction->transaction_number);
        $this->assertSame(KasTransactionType::Keluar, $transaction->transaction_type);

        // Saldo awal 500.000 - 150.000 = 350.000
        $this->kasToko->refresh();
        $this->assertEquals(350_000.00, $this->kasToko->current_balance);
    }

    public function test_kas_keluar_throws_exception_when_balance_insufficient(): void
    {
        $this->expectException(InsufficientCashBalanceException::class);

        // Saldo kas toko hanya 500.000, coba keluarkan 1.000.000
        $context = new KasKeluarContext(
            kasAccountId: $this->kasToko->id,
            branchId: $this->branch->id,
            paymentMethod: PaymentMethodType::Tunai,
            amount: 1_000_000.00,
            description: 'Penarikan melebihi saldo',
        );

        $this->kasKeluarService->execute($context);
    }

    public function test_kas_mutasi_transfers_balance_between_accounts_atomically(): void
    {
        // Setor tunai dari Kasir Toko ke Bank Mandiri sebesar Rp 300.000 (biaya admin 2.500)
        $context = new KasMutasiContext(
            fromKasAccountId: $this->kasToko->id,
            toKasAccountId: $this->bankMandiri->id,
            amount: 300_000.00,
            adminFee: 2_500.00,
            description: 'Setor penerimaan kasir hari ini ke Bank',
        );

        $transfer = $this->kasMutasiService->execute($context);

        $this->assertNotNull($transfer->id);
        $this->assertStringStartsWith('TRF-', $transfer->transfer_number);
        $this->assertEquals(302_500.00, $transfer->totalDeduction());

        // Akun asal: 500.000 - 302.500 = 197.500
        $this->kasToko->refresh();
        $this->assertEquals(197_500.00, $this->kasToko->current_balance);

        // Akun tujuan: 10.000.000 + 300.000 = 10.300.000
        $this->bankMandiri->refresh();
        $this->assertEquals(10_300_000.00, $this->bankMandiri->current_balance);
    }

    public function test_kas_mutasi_rejects_same_account(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $context = new KasMutasiContext(
            fromKasAccountId: $this->kasToko->id,
            toKasAccountId: $this->kasToko->id,
            amount: 50_000.00,
        );

        $this->kasMutasiService->execute($context);
    }

    public function test_kas_mutasi_rejects_insufficient_source_balance(): void
    {
        $this->expectException(InsufficientCashBalanceException::class);

        // Saldo kas toko 500.000, coba mutasi 600.000
        $context = new KasMutasiContext(
            fromKasAccountId: $this->kasToko->id,
            toKasAccountId: $this->bankMandiri->id,
            amount: 600_000.00,
        );

        $this->kasMutasiService->execute($context);
    }
}
