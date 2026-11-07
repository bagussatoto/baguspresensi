<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Kas\Domain\Enums\AccountCategory;
use Modules\Kas\Domain\Enums\NormalBalance;
use Modules\Kas\Domain\Models\ChartOfAccount;
use Modules\Kas\Domain\Models\KasAccount;
use Modules\MasterData\Domain\Models\Branch;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branchId = Branch::first()?->id;

        $accounts = [
            // 1. ASET
            [
                'code' => '1-00000',
                'name' => 'ASET / AKTIVA',
                'category' => AccountCategory::ASSET,
                'normal' => NormalBalance::DEBIT,
                'level' => 1,
                'is_header' => true,
                'parent' => null,
            ],
            [
                'code' => '1-10000',
                'name' => 'Aset Lancar',
                'category' => AccountCategory::ASSET,
                'normal' => NormalBalance::DEBIT,
                'level' => 2,
                'is_header' => true,
                'parent' => '1-00000',
            ],
            [
                'code' => '1-10001',
                'name' => 'Kas Laci Kasir (Outlet)',
                'category' => AccountCategory::ASSET,
                'normal' => NormalBalance::DEBIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '1-10000',
                'description' => 'Uang tunai kasir toko / laci kas shift',
            ],
            [
                'code' => '1-10002',
                'name' => 'Kas Operasional Kantor',
                'category' => AccountCategory::ASSET,
                'normal' => NormalBalance::DEBIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '1-10000',
                'description' => 'Kas kecil petty cash operasional outlet',
            ],
            [
                'code' => '1-11001',
                'name' => 'Rekening Bank BCA',
                'category' => AccountCategory::ASSET,
                'normal' => NormalBalance::DEBIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '1-10000',
                'description' => 'Penerimaan QRIS & transfer bank',
            ],
            [
                'code' => '1-11002',
                'name' => 'Rekening Bank Mandiri',
                'category' => AccountCategory::ASSET,
                'normal' => NormalBalance::DEBIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '1-10000',
            ],
            [
                'code' => '1-12001',
                'name' => 'Piutang Usaha Pelanggan',
                'category' => AccountCategory::ASSET,
                'normal' => NormalBalance::DEBIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '1-10000',
            ],
            [
                'code' => '1-13001',
                'name' => 'Persediaan Barang Dagang FnB',
                'category' => AccountCategory::ASSET,
                'normal' => NormalBalance::DEBIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '1-10000',
                'description' => 'Stok makanan & minuman cafe',
            ],
            [
                'code' => '1-13002',
                'name' => 'Persediaan Kertas & Tinta Print',
                'category' => AccountCategory::ASSET,
                'normal' => NormalBalance::DEBIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '1-10000',
                'description' => 'Stok kertas A4, F4 & toner printer',
            ],
            [
                'code' => '1-20000',
                'name' => 'Aset Tetap',
                'category' => AccountCategory::ASSET,
                'normal' => NormalBalance::DEBIT,
                'level' => 2,
                'is_header' => true,
                'parent' => '1-00000',
            ],
            [
                'code' => '1-20001',
                'name' => 'Peralatan Komputer PC Station & Server',
                'category' => AccountCategory::ASSET,
                'normal' => NormalBalance::DEBIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '1-20000',
            ],
            [
                'code' => '1-20002',
                'name' => 'Printer & Perangkat POS Kasir',
                'category' => AccountCategory::ASSET,
                'normal' => NormalBalance::DEBIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '1-20000',
            ],

            // 2. KEWAJIBAN / HUTANG
            [
                'code' => '2-00000',
                'name' => 'KEWAJIBAN / HUTANG',
                'category' => AccountCategory::LIABILITY,
                'normal' => NormalBalance::CREDIT,
                'level' => 1,
                'is_header' => true,
                'parent' => null,
            ],
            [
                'code' => '2-10000',
                'name' => 'Kewajiban Jangka Pendek',
                'category' => AccountCategory::LIABILITY,
                'normal' => NormalBalance::CREDIT,
                'level' => 2,
                'is_header' => true,
                'parent' => '2-00000',
            ],
            [
                'code' => '2-10001',
                'name' => 'Hutang Usaha / Supplier F&B',
                'category' => AccountCategory::LIABILITY,
                'normal' => NormalBalance::CREDIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '2-10000',
            ],
            [
                'code' => '2-10002',
                'name' => 'Titipan / Deposit Member Billing',
                'category' => AccountCategory::LIABILITY,
                'normal' => NormalBalance::CREDIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '2-10000',
            ],

            // 3. EKUITAS / MODAL
            [
                'code' => '3-00000',
                'name' => 'EKUITAS / MODAL',
                'category' => AccountCategory::EQUITY,
                'normal' => NormalBalance::CREDIT,
                'level' => 1,
                'is_header' => true,
                'parent' => null,
            ],
            [
                'code' => '3-10001',
                'name' => 'Modal Disetor Pemilik',
                'category' => AccountCategory::EQUITY,
                'normal' => NormalBalance::CREDIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '3-00000',
            ],
            [
                'code' => '3-20001',
                'name' => 'Laba Ditahan',
                'category' => AccountCategory::EQUITY,
                'normal' => NormalBalance::CREDIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '3-00000',
            ],
            [
                'code' => '3-30001',
                'name' => 'Laba / Rugi Berjalan',
                'category' => AccountCategory::EQUITY,
                'normal' => NormalBalance::CREDIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '3-00000',
            ],

            // 4. PENDAPATAN / OMZET
            [
                'code' => '4-00000',
                'name' => 'PENDAPATAN USAHA',
                'category' => AccountCategory::REVENUE,
                'normal' => NormalBalance::CREDIT,
                'level' => 1,
                'is_header' => true,
                'parent' => null,
            ],
            [
                'code' => '4-10001',
                'name' => 'Pendapatan Penjualan F&B',
                'category' => AccountCategory::REVENUE,
                'normal' => NormalBalance::CREDIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '4-00000',
                'description' => 'Penjualan makanan dan minuman kasir POS',
            ],
            [
                'code' => '4-10002',
                'name' => 'Pendapatan Billing Internet PC Station',
                'category' => AccountCategory::REVENUE,
                'normal' => NormalBalance::CREDIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '4-00000',
                'description' => 'Pendapatan sewa PC game & internet warnet',
            ],
            [
                'code' => '4-10003',
                'name' => 'Pendapatan Jasa Cetak & Print Kertas',
                'category' => AccountCategory::REVENUE,
                'normal' => NormalBalance::CREDIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '4-00000',
                'description' => 'Layanan cetak dokumen & fotokopi',
            ],

            // 5. HARGA POKOK PENJUALAN (HPP)
            [
                'code' => '5-00000',
                'name' => 'HARGA POKOK PENJUALAN',
                'category' => AccountCategory::COGS,
                'normal' => NormalBalance::DEBIT,
                'level' => 1,
                'is_header' => true,
                'parent' => null,
            ],
            [
                'code' => '5-10001',
                'name' => 'HPP Makanan & Minuman',
                'category' => AccountCategory::COGS,
                'normal' => NormalBalance::DEBIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '5-00000',
            ],
            [
                'code' => '5-10002',
                'name' => 'HPP Kertas & Tinta Printer',
                'category' => AccountCategory::COGS,
                'normal' => NormalBalance::DEBIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '5-00000',
            ],

            // 6. BEBAN OPERASIONAL
            [
                'code' => '6-00000',
                'name' => 'BEBAN OPERASIONAL',
                'category' => AccountCategory::EXPENSE,
                'normal' => NormalBalance::DEBIT,
                'level' => 1,
                'is_header' => true,
                'parent' => null,
            ],
            [
                'code' => '6-10001',
                'name' => 'Beban Listrik PLN Outlet',
                'category' => AccountCategory::EXPENSE,
                'normal' => NormalBalance::DEBIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '6-00000',
            ],
            [
                'code' => '6-10002',
                'name' => 'Beban Internet & Bandwidth ISP',
                'category' => AccountCategory::EXPENSE,
                'normal' => NormalBalance::DEBIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '6-00000',
            ],
            [
                'code' => '6-10003',
                'name' => 'Beban Gaji & Upah Karyawan',
                'category' => AccountCategory::EXPENSE,
                'normal' => NormalBalance::DEBIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '6-00000',
            ],
            [
                'code' => '6-10004',
                'name' => 'Beban Sanitasi & General Cleaning (GC)',
                'category' => AccountCategory::EXPENSE,
                'normal' => NormalBalance::DEBIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '6-00000',
            ],
            [
                'code' => '6-10005',
                'name' => 'Beban Pemeliharaan PC Station & Hardware',
                'category' => AccountCategory::EXPENSE,
                'normal' => NormalBalance::DEBIT,
                'level' => 3,
                'is_header' => false,
                'parent' => '6-00000',
            ],
        ];

        $createdAccounts = [];

        foreach ($accounts as $acc) {
            $parentId = null;
            if (! empty($acc['parent']) && isset($createdAccounts[$acc['parent']])) {
                $parentId = $createdAccounts[$acc['parent']]->id;
            }

            $coa = ChartOfAccount::updateOrCreate(
                ['account_code' => $acc['code']],
                [
                    'branch_id' => $branchId,
                    'account_name' => $acc['name'],
                    'account_category' => $acc['category'],
                    'normal_balance' => $acc['normal'],
                    'parent_id' => $parentId,
                    'level' => $acc['level'],
                    'is_header' => $acc['is_header'],
                    'is_active' => true,
                    'description' => $acc['description'] ?? null,
                ]
            );

            $createdAccounts[$acc['code']] = $coa;
        }

        // Tautkan akun kasir yang ada ke akun COA Kas Laci Kasir 1-10001
        $kasKasirCoa = $createdAccounts['1-10001'] ?? null;
        if ($kasKasirCoa) {
            KasAccount::where('type', 'kasir_toko')
                ->whereNull('chart_of_account_id')
                ->update(['chart_of_account_id' => $kasKasirCoa->id]);
        }

        // Tautkan akun kas operasional ke 1-10002
        $kasOpCoa = $createdAccounts['1-10002'] ?? null;
        if ($kasOpCoa) {
            KasAccount::where('type', 'kas_operasional')
                ->whereNull('chart_of_account_id')
                ->update(['chart_of_account_id' => $kasOpCoa->id]);
        }

        // Tautkan akun bank ke 1-11001
        $bankCoa = $createdAccounts['1-11001'] ?? null;
        if ($bankCoa) {
            KasAccount::where('type', 'bank')
                ->whereNull('chart_of_account_id')
                ->update(['chart_of_account_id' => $bankCoa->id]);
        }
    }
}
