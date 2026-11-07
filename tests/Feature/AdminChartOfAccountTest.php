<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Kas\Domain\Enums\AccountCategory;
use Modules\Kas\Domain\Enums\NormalBalance;
use Modules\Kas\Domain\Models\ChartOfAccount;
use Tests\TestCase;

class AdminChartOfAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_coa_page_can_be_rendered(): void
    {
        ChartOfAccount::create([
            'account_code' => '1-00000',
            'account_name' => 'ASET',
            'account_category' => AccountCategory::ASSET,
            'normal_balance' => NormalBalance::DEBIT,
            'level' => 1,
            'is_header' => true,
            'is_active' => true,
        ]);

        $response = $this->get(route('admin.coa'));

        $response->assertStatus(200);
        $response->assertSee('Bagan Akun');
        $response->assertSee('1-00000');
        $response->assertSee('ASET');
    }

    public function test_admin_can_fetch_coa_via_json(): void
    {
        ChartOfAccount::create([
            'account_code' => '1-10001',
            'account_name' => 'Kas Kasir POS Utama',
            'account_category' => AccountCategory::ASSET,
            'normal_balance' => NormalBalance::DEBIT,
            'level' => 3,
            'is_header' => false,
            'is_active' => true,
        ]);

        $response = $this->getJson(route('admin.coa'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonFragment([
            'account_code' => '1-10001',
            'account_name' => 'Kas Kasir POS Utama',
        ]);
    }

    public function test_admin_can_create_new_coa_account(): void
    {
        $payload = [
            'account_code' => '6-10099',
            'account_name' => 'Beban Serba-Serbi Operasional',
            'account_category' => AccountCategory::EXPENSE->value,
            'normal_balance' => NormalBalance::DEBIT->value,
            'is_header' => 0,
            'description' => 'Beban serba serbi untuk uji unit.',
        ];

        $response = $this->post(route('admin.coa.store'), $payload);

        $response->assertRedirect(route('admin.coa'));
        $this->assertDatabaseHas('chart_of_accounts', [
            'account_code' => '6-10099',
            'account_name' => 'Beban Serba-Serbi Operasional',
        ]);
    }

    public function test_admin_can_update_coa_account(): void
    {
        $account = ChartOfAccount::create([
            'account_code' => '1-12001',
            'account_name' => 'Piutang Billing Lama',
            'account_category' => AccountCategory::ASSET,
            'normal_balance' => NormalBalance::DEBIT,
            'level' => 2,
            'is_header' => false,
            'is_active' => true,
        ]);

        $payload = [
            'account_code' => '1-12001',
            'account_name' => 'Piutang Member Billing Internet',
            'account_category' => AccountCategory::ASSET->value,
            'normal_balance' => NormalBalance::DEBIT->value,
            'is_header' => 0,
            'description' => 'Diperbarui via test',
        ];

        $response = $this->put(route('admin.coa.update', $account->id), $payload);

        $response->assertRedirect(route('admin.coa'));
        $this->assertDatabaseHas('chart_of_accounts', [
            'id' => $account->id,
            'account_name' => 'Piutang Member Billing Internet',
        ]);
    }

    public function test_admin_can_toggle_account_status(): void
    {
        $account = ChartOfAccount::create([
            'account_code' => '4-99999',
            'account_name' => 'Pendapatan Lain-Lain Temp',
            'account_category' => AccountCategory::REVENUE,
            'normal_balance' => NormalBalance::CREDIT,
            'level' => 2,
            'is_header' => false,
            'is_active' => true,
        ]);

        $response = $this->post(route('admin.coa.toggle-status', $account->id));

        $response->assertRedirect(route('admin.coa'));
        $this->assertDatabaseHas('chart_of_accounts', [
            'id' => $account->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_leaf_account_without_children(): void
    {
        $account = ChartOfAccount::create([
            'account_code' => '6-99998',
            'account_name' => 'Beban Dummy Dihapus',
            'account_category' => AccountCategory::EXPENSE,
            'normal_balance' => NormalBalance::DEBIT,
            'level' => 2,
            'is_header' => false,
            'is_active' => true,
            'current_balance' => 0,
        ]);

        $response = $this->delete(route('admin.coa.destroy', $account->id));

        $response->assertRedirect(route('admin.coa'));
        $this->assertDatabaseMissing('chart_of_accounts', [
            'id' => $account->id,
        ]);
    }

    public function test_cannot_delete_header_account_with_children(): void
    {
        $parent = ChartOfAccount::create([
            'account_code' => '1-00000',
            'account_name' => 'ASET UTAMA',
            'account_category' => AccountCategory::ASSET,
            'normal_balance' => NormalBalance::DEBIT,
            'level' => 1,
            'is_header' => true,
            'is_active' => true,
        ]);

        ChartOfAccount::create([
            'account_code' => '1-10000',
            'account_name' => 'Aset Lancar',
            'account_category' => AccountCategory::ASSET,
            'normal_balance' => NormalBalance::DEBIT,
            'parent_id' => $parent->id,
            'level' => 2,
            'is_header' => true,
            'is_active' => true,
        ]);

        $response = $this->delete(route('admin.coa.destroy', $parent->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('chart_of_accounts', [
            'id' => $parent->id,
        ]);
    }
}
