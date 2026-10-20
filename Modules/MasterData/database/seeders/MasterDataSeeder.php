<?php

declare(strict_types=1);

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Domain\Enums\CustomerType;
use Modules\MasterData\Domain\Enums\TaxIdType;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Customer;
use Modules\MasterData\Domain\Models\Pricelist;
use Modules\MasterData\Domain\Models\UnitOfMeasurement;
use Modules\MasterData\Domain\Models\Warehouse;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cabang Tunggal Utama: Maya Graha Kencana
        $branchMgk = Branch::firstOrCreate(
            ['code' => 'NET-PST'],
            [
                'name' => 'Maya Graha Kencana',
                'is_headquarters' => true,
                'address' => 'Jl. Kaliurang KM 5.2 No. 88, Sleman, D.I. Yogyakarta',
                'phone' => '0274-556789',
                'is_active' => true,
            ]
        );

        // Gudang Terkait Maya Graha Kencana
        Warehouse::firstOrCreate(
            ['code' => 'GDG-MGK-01'],
            [
                'branch_id' => $branchMgk->id,
                'name' => 'Gudang Utama Maya Graha Kencana',
                'type' => 'storage',
                'address' => 'Jl. Kaliurang KM 5.2 No. 88, Sleman, D.I. Yogyakarta',
                'is_active' => true,
            ]
        );

        // 2. Satuan Unit Pengukuran (UOM) Standar
        UnitOfMeasurement::firstOrCreate(
            ['code' => 'PCS'],
            ['name' => 'Pieces / Buah', 'symbol' => 'pcs', 'is_active' => true]
        );

        UnitOfMeasurement::firstOrCreate(
            ['code' => 'JAM'],
            ['name' => 'Jam Akses / Billing', 'symbol' => 'jam', 'is_active' => true]
        );

        UnitOfMeasurement::firstOrCreate(
            ['code' => 'PKT'],
            ['name' => 'Paket Billing', 'symbol' => 'pkt', 'is_active' => true]
        );

        UnitOfMeasurement::firstOrCreate(
            ['code' => 'PORSI'],
            ['name' => 'Porsi / Mangkuk', 'symbol' => 'prs', 'is_active' => true]
        );

        UnitOfMeasurement::firstOrCreate(
            ['code' => 'CUP'],
            ['name' => 'Cup / Gelas', 'symbol' => 'cup', 'is_active' => true]
        );

        UnitOfMeasurement::firstOrCreate(
            ['code' => 'BTL'],
            ['name' => 'Botol Dingin', 'symbol' => 'btl', 'is_active' => true]
        );

        // 3. Daftar Harga (Pricelists)
        $plRetail = Pricelist::firstOrCreate(
            ['code' => 'PL-RETAIL'],
            [
                'name' => 'Daftar Harga Eceran / Toko Kasir',
                'customer_type' => CustomerType::RETAIL,
                'currency' => 'IDR',
                'is_active' => true,
            ]
        );

        $plGrosir = Pricelist::firstOrCreate(
            ['code' => 'PL-GROSIR'],
            [
                'name' => 'Daftar Harga Member & Grosir',
                'customer_type' => CustomerType::GROSIR,
                'currency' => 'IDR',
                'is_active' => true,
            ]
        );

        // 4. Konsumen Default
        Customer::firstOrCreate(
            ['code' => 'CUST-001'],
            [
                'name' => 'Komunitas Gamers Regular Jogja',
                'customer_type' => CustomerType::GROSIR,
                'pricelist_id' => $plGrosir->id,
                'tax_id_type' => TaxIdType::NPWP_16,
                'tax_id_number' => '0123456789012345',
                'credit_limit' => 15000000.0,
                'current_credit_used' => 2500000.0,
                'payment_term_days' => 14,
                'phone' => '0812-3344-5566',
                'email' => 'member.gamers@mayanet.com',
                'address' => 'Jl. Kaliurang KM 5, Sleman',
                'is_active' => true,
            ]
        );

        Customer::firstOrCreate(
            ['code' => 'CUST-002'],
            [
                'name' => 'Pengunjung Reguler Walk-in (POS)',
                'customer_type' => CustomerType::RETAIL,
                'pricelist_id' => $plRetail->id,
                'tax_id_type' => TaxIdType::NIK,
                'tax_id_number' => '3201012304850001',
                'credit_limit' => 0.0,
                'current_credit_used' => 0.0,
                'payment_term_days' => 0,
                'phone' => '0813-9988-7711',
                'email' => 'walkin@mayanet.com',
                'address' => 'Sleman, Yogyakarta',
                'is_active' => true,
            ]
        );
    }
}
