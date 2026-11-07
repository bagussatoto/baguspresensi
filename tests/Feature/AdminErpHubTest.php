<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminErpHubTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_erp_hub_can_be_rendered(): void
    {
        $response = $this->get(route('admin.erp'));

        $response->assertStatus(200);
        $response->assertSee('Pusat Kendali ERP Maya Graha Kencana');
        $response->assertSee('Master Data &amp; Katalog Terpadu', false);
        $response->assertSee('POS Desktop Kasir &amp; Billing', false);
        $response->assertSee('Bagan Akun (Chart of Accounts / COA)');
        $response->assertSee('Persediaan, Gudang &amp; Distribusi', false);
        $response->assertSee('Sumber Daya Manusia (HRM) &amp; Presensi', false);
        $response->assertSee('Keamanan, Tata Kelola &amp; Audit Trail', false);
    }

    public function test_admin_erp_hub_alias_route_can_be_rendered(): void
    {
        $response = $this->get('/admin/ERP');
        $response->assertStatus(200);

        $responseLower = $this->get('/admin/erp');
        $responseLower->assertStatus(200);
    }

    public function test_admin_erp_hub_can_fetch_json_metrics(): void
    {
        $response = $this->getJson(route('admin.erp'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'metrics' => [
                'total_sku',
                'active_employees',
                'today_sales_count',
                'today_sales_amount',
                'total_stock',
                'coa_accounts',
            ],
            'pillars' => [
                'master_data',
                'pos_sales',
                'supply_chain',
                'human_resource',
                'finance_accounting',
                'security_governance',
            ],
        ]);
    }
}
