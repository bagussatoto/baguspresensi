<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kas\Domain\Enums\KasAccountType;
use Modules\Kas\Domain\Enums\PaymentMethodType;
use Modules\Kas\Domain\Models\KasAccount;
use Modules\MasterData\Domain\Models\Branch;
use Tests\TestCase;

class KasApiTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'code' => 'BR-API-01',
            'name' => 'Cabang Jakarta Selatan',
        ]);
    }

    public function test_can_create_kas_account_via_api(): void
    {
        $payload = [
            'branch_id' => $this->branch->id,
            'code' => 'KAS-LACI-01',
            'name' => 'Laci Kasir 1',
            'type' => KasAccountType::KasirToko->value,
            'current_balance' => 200_000.00,
        ];

        $response = $this->postJson(route('kas.accounts.store'), $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.code', 'KAS-LACI-01')
            ->assertJsonPath('data.name', 'Laci Kasir 1');

        $this->assertDatabaseHas('kas_accounts', [
            'code' => 'KAS-LACI-01',
        ]);
    }

    public function test_can_record_kas_masuk_via_api(): void
    {
        $account = KasAccount::create([
            'branch_id' => $this->branch->id,
            'code' => 'KAS-01',
            'name' => 'Kasir Utama',
            'type' => KasAccountType::KasirToko,
            'current_balance' => 100_000.00,
        ]);

        $payload = [
            'kas_account_id' => $account->id,
            'branch_id' => $this->branch->id,
            'payment_method' => PaymentMethodType::Tunai->value,
            'amount' => 50_000.00,
            'reference_number' => 'POS-9988',
            'description' => 'Pembayaran struk belanja',
        ];

        $response = $this->postJson(route('kas.masuk.store'), $payload);

        $response->assertStatus(201);
        $this->assertEquals(50_000.00, $response->json('data.amount'));

        $account->refresh();
        $this->assertEquals(150_000.00, $account->current_balance);
    }

    public function test_can_record_kas_keluar_via_api_and_guards_balance(): void
    {
        $account = KasAccount::create([
            'branch_id' => $this->branch->id,
            'code' => 'KAS-02',
            'name' => 'Kasir 2',
            'type' => KasAccountType::KasirToko,
            'current_balance' => 100_000.00,
        ]);

        // Coba keluar melebihi saldo -> 422
        $excessPayload = [
            'kas_account_id' => $account->id,
            'branch_id' => $this->branch->id,
            'payment_method' => PaymentMethodType::Tunai->value,
            'amount' => 200_000.00,
            'description' => 'Biaya darurat melebihi saldo',
        ];

        $this->postJson(route('kas.keluar.store'), $excessPayload)
            ->assertStatus(422);

        // Pengeluaran valid
        $validPayload = [
            'kas_account_id' => $account->id,
            'branch_id' => $this->branch->id,
            'payment_method' => PaymentMethodType::Tunai->value,
            'amount' => 30_000.00,
            'description' => 'Beli bensin operasional',
        ];

        $this->postJson(route('kas.keluar.store'), $validPayload)
            ->assertStatus(201);

        $account->refresh();
        $this->assertEquals(70_000.00, $account->current_balance);
    }

    public function test_can_process_kas_mutasi_via_api(): void
    {
        $fromAccount = KasAccount::create([
            'branch_id' => $this->branch->id,
            'code' => 'KAS-FROM',
            'name' => 'Kasir Toko',
            'type' => KasAccountType::KasirToko,
            'current_balance' => 500_000.00,
        ]);

        $toAccount = KasAccount::create([
            'branch_id' => $this->branch->id,
            'code' => 'BANK-TO',
            'name' => 'Bank BCA',
            'type' => KasAccountType::Bank,
            'current_balance' => 1_000_000.00,
        ]);

        $payload = [
            'from_kas_account_id' => $fromAccount->id,
            'to_kas_account_id' => $toAccount->id,
            'amount' => 200_000.00,
            'admin_fee' => 5_000.00,
            'description' => 'Setor tunai ke rekening bank BCA',
        ];

        $response = $this->postJson(route('kas.mutasi.store'), $payload);

        $response->assertStatus(201);

        $fromAccount->refresh();
        $toAccount->refresh();

        $this->assertEquals(295_000.00, $fromAccount->current_balance);
        $this->assertEquals(1_200_000.00, $toAccount->current_balance);
    }
}
