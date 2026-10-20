<?php

declare(strict_types=1);

namespace Modules\MasterData\Database\Seeders;

use App\Models\User;
use Database\Seeders\AttendanceMasterSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\MasterData\Domain\Enums\EmployeeRole;
use Modules\MasterData\Domain\Models\Attendance;
use Modules\MasterData\Domain\Models\Branch;
use Modules\MasterData\Domain\Models\Brand;
use Modules\MasterData\Domain\Models\CafePayment;
use Modules\MasterData\Domain\Models\CafeSale;
use Modules\MasterData\Domain\Models\CafeSaleItem;
use Modules\MasterData\Domain\Models\Category;
use Modules\MasterData\Domain\Models\Commission;
use Modules\MasterData\Domain\Models\Employee;
use Modules\MasterData\Domain\Models\EmployeeSchedule;
use Modules\MasterData\Domain\Models\PcStation;
use Modules\MasterData\Domain\Models\Product;
use Modules\MasterData\Domain\Models\ProductVariant;
use Modules\MasterData\Domain\Models\StockInventory;
use Modules\MasterData\Domain\Models\StockTransferDispatch;
use Modules\MasterData\Domain\Models\UnitOfMeasurement;
use Modules\MasterData\Domain\Models\Warehouse;

class InternetCafeSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Akun Administrator Default Maya Graha Kencana
        User::firstOrCreate(
            ['email' => 'admin@mayanet.com'],
            [
                'name' => 'Admin Maya Graha Kencana',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        // 1. Cabang & Pos Kasir Maya Graha Kencana
        $branchWarnet = Branch::firstOrCreate(
            ['code' => 'NET-PST'],
            [
                'name' => 'Maya Graha Kencana',
                'is_headquarters' => true,
                'address' => 'Jl. Kaliurang KM 5.2 No. 88, Sleman, D.I. Yogyakarta',
                'phone' => '0274-556789',
                'is_active' => true,
            ]
        );

        $posWarehouse = Warehouse::firstOrCreate(
            ['code' => 'GDG-KASIR-01'],
            [
                'branch_id' => $branchWarnet->id,
                'name' => 'Loker & Etalase Kasir Warnet',
                'type' => 'storage',
                'address' => 'Frontdesk Kasir Utama',
                'is_active' => true,
            ]
        );

        $kitchenWarehouse = Warehouse::firstOrCreate(
            ['code' => 'GDG-DAPUR-01'],
            [
                'branch_id' => $branchWarnet->id,
                'name' => 'Gudang Dapur & Stok Pusat F&B',
                'type' => 'storage',
                'address' => 'Dapur & Pantry Belakang',
                'is_active' => true,
            ]
        );

        // 2. Kategori Produk Warnet
        $catBilling = Category::firstOrCreate(
            ['code' => 'CAT-BILLING'],
            ['name' => 'Billing & Sewa PC Internet', 'slug' => 'billing-internet', 'is_active' => true]
        );

        $catFood = Category::firstOrCreate(
            ['code' => 'CAT-FOOD'],
            ['name' => 'Makanan & Mie Instan (F&B)', 'slug' => 'makanan-fnb', 'is_active' => true]
        );

        $catDrink = Category::firstOrCreate(
            ['code' => 'CAT-DRINK'],
            ['name' => 'Minuman Dingin & Kopi (F&B)', 'slug' => 'minuman-fnb', 'is_active' => true]
        );

        $catSnack = Category::firstOrCreate(
            ['code' => 'CAT-SNACK'],
            ['name' => 'Snack & Cemilan Gamers', 'slug' => 'snack-cemilan', 'is_active' => true]
        );

        // Merek
        $brandCyberCore = Brand::firstOrCreate(
            ['code' => 'BRD-CYBER'],
            ['name' => 'MayaNet Gaming Systems', 'slug' => 'mayanet', 'is_active' => true]
        );

        $brandIndofood = Brand::firstOrCreate(
            ['code' => 'BRD-INDOFOOD'],
            ['name' => 'Indofood', 'slug' => 'indofood', 'is_active' => true]
        );

        $brandMayora = Brand::firstOrCreate(
            ['code' => 'BRD-MAYORA'],
            ['name' => 'Mayora', 'slug' => 'mayora', 'is_active' => true]
        );

        // Satuan
        $uomJam = UnitOfMeasurement::firstOrCreate(
            ['code' => 'JAM'],
            ['name' => 'Jam Billing', 'symbol' => 'jam', 'is_active' => true]
        );

        $uomPorsi = UnitOfMeasurement::firstOrCreate(
            ['code' => 'PRG'],
            ['name' => 'Porsi / Piring', 'symbol' => 'porsi', 'is_active' => true]
        );

        $uomCup = UnitOfMeasurement::firstOrCreate(
            ['code' => 'CUP'],
            ['name' => 'Cup / Gelas', 'symbol' => 'cup', 'is_active' => true]
        );

        $uomBtl = UnitOfMeasurement::firstOrCreate(
            ['code' => 'BTL'],
            ['name' => 'Botol', 'symbol' => 'btl', 'is_active' => true]
        );

        $uomBks = UnitOfMeasurement::firstOrCreate(
            ['code' => 'BKS'],
            ['name' => 'Bungkus / Pcs', 'symbol' => 'bks', 'is_active' => true]
        );

        // 3. Produk & Varian (Billing, F&B, Minuman)
        // Billing Reguler
        $pBillingReguler = Product::firstOrCreate(
            ['code' => 'PRD-BILL-REG'],
            [
                'category_id' => $catBilling->id,
                'brand_id' => $brandCyberCore->id,
                'uom_id' => $uomJam->id,
                'name' => 'Billing PC Reguler (Core i5 / GTX 1660 / 144Hz)',
                'description' => 'Akses internet & gaming PC Reguler',
                'is_active' => true,
            ]
        );
        $vBillReg1 = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-BILL-REG-1H'],
            ['product_id' => $pBillingReguler->id, 'barcode' => 'BIL001', 'name' => 'Billing Reguler 1 Jam', 'weight_grams' => 0, 'base_price' => 6000.0, 'cost_price' => 0.0]
        );
        $vBillReg3 = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-BILL-REG-3H'],
            ['product_id' => $pBillingReguler->id, 'barcode' => 'BIL003', 'name' => 'Paket Hemat Reguler 3 Jam', 'weight_grams' => 0, 'base_price' => 16000.0, 'cost_price' => 0.0]
        );
        $vBillNight = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-BILL-REG-NIGHT'],
            ['product_id' => $pBillingReguler->id, 'barcode' => 'BIL006', 'name' => 'Paket Begadang Malam 6 Jam (23:00-05:00)', 'weight_grams' => 0, 'base_price' => 25000.0, 'cost_price' => 0.0]
        );

        // Billing VIP RTX 4070
        $pBillingVip = Product::firstOrCreate(
            ['code' => 'PRD-BILL-VIP'],
            [
                'category_id' => $catBilling->id,
                'brand_id' => $brandCyberCore->id,
                'uom_id' => $uomJam->id,
                'name' => 'Billing PC VIP Esports (Ryzen 7 / RTX 4070 / 240Hz)',
                'description' => 'Bilik gaming VIP kursi ergonomis dan monitor 240Hz',
                'is_active' => true,
            ]
        );
        $vBillVip1 = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-BILL-VIP-1H'],
            ['product_id' => $pBillingVip->id, 'barcode' => 'BILVIP01', 'name' => 'Billing VIP Esports 1 Jam', 'weight_grams' => 0, 'base_price' => 10000.0, 'cost_price' => 0.0]
        );
        $vBillVip3 = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-BILL-VIP-3H'],
            ['product_id' => $pBillingVip->id, 'barcode' => 'BILVIP03', 'name' => 'Paket VIP Esports 3 Jam', 'weight_grams' => 0, 'base_price' => 27000.0, 'cost_price' => 0.0]
        );

        // -------------------------------------------------------------
        // F&B: MENU MAKANAN KHAS WARMINDO & GAMERS WARNET JOGJA
        // -------------------------------------------------------------
        $brandWarmindo = Brand::firstOrCreate(
            ['code' => 'BRD-WARMINDO'],
            ['name' => 'Warmindo MayaNet Kitchen', 'slug' => 'warmindo-mayanet', 'is_active' => true]
        );

        // 1. Indomie Dok-Dok Nyemek Telur Pedas
        $pDokdok = Product::firstOrCreate(
            ['code' => 'PRD-FNB-DOKDOK'],
            [
                'category_id' => $catFood->id,
                'brand_id' => $brandIndofood->id,
                'uom_id' => $uomPorsi->id,
                'name' => 'Indomie Dok-Dok Nyemek Telur Pedas',
                'description' => 'Kuah kental dok-dok khas Warmindo Jogja, orak-arik telur ayam, caisim segar, dan cabai rawit pedas nampol',
                'is_active' => true,
            ]
        );
        $vDokdokOri = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-FNB-DOKDOK-ORI'],
            ['product_id' => $pDokdok->id, 'barcode' => 'FNB-DKDK-01', 'name' => 'Indomie Dok-Dok Nyemek Telur Pedas', 'weight_grams' => 250, 'base_price' => 14000.0, 'cost_price' => 7500.0]
        );
        $vDokdokJumbo = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-FNB-DOKDOK-DBL'],
            ['product_id' => $pDokdok->id, 'barcode' => 'FNB-DKDK-02', 'name' => 'Indomie Dok-Dok Double Jumbo + Sosis', 'weight_grams' => 380, 'base_price' => 19000.0, 'cost_price' => 10000.0]
        );

        // 2. Indomie Goreng (Internet / Telur Kornet)
        $pIndomie = Product::firstOrCreate(
            ['code' => 'PRD-FNB-INDOMIE'],
            [
                'category_id' => $catFood->id,
                'brand_id' => $brandIndofood->id,
                'uom_id' => $uomPorsi->id,
                'name' => 'Indomie Goreng Double Kornet Keju (Internet)',
                'description' => 'Menu legendaris warnet: porsi double Indomie goreng, kornet tumis gurih, dan parutan keju cheddar melimpah',
                'is_active' => true,
            ]
        );
        $vIndomieTelur = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-FNB-MIE-TLR'],
            ['product_id' => $pIndomie->id, 'barcode' => 'FNB001', 'name' => 'Indomie Goreng Single + Telur Mata Sapi', 'weight_grams' => 200, 'base_price' => 12000.0, 'cost_price' => 6000.0]
        );
        $vIndomieKornet = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-FNB-MIE-KOR'],
            ['product_id' => $pIndomie->id, 'barcode' => 'FNB002', 'name' => 'Indomie Goreng Double Kornet Keju (Internet)', 'weight_grams' => 350, 'base_price' => 18000.0, 'cost_price' => 9500.0]
        );

        // 3. Nasi Magelangan / Nasi Ruwet Jogja
        $pMagelangan = Product::firstOrCreate(
            ['code' => 'PRD-FNB-MAGELANGAN'],
            [
                'category_id' => $catFood->id,
                'brand_id' => $brandWarmindo->id,
                'uom_id' => $uomPorsi->id,
                'name' => 'Nasi Magelangan (Nasi Ruwet) Warmindo',
                'description' => 'Nasi goreng racik mie Indomie khas Jogja dengan telur orak-arik dan kerupuk renyah',
                'is_active' => true,
            ]
        );
        $vMagelangan = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-FNB-MGL-TELUR'],
            ['product_id' => $pMagelangan->id, 'barcode' => 'FNB-MGL-01', 'name' => 'Nasi Magelangan Telur Ceplok Spesial', 'weight_grams' => 350, 'base_price' => 16000.0, 'cost_price' => 8000.0]
        );

        // 4. Nasi Ayam Geprek Sambal Bawang Jakal
        $pGeprek = Product::firstOrCreate(
            ['code' => 'PRD-FNB-GEPREK'],
            [
                'category_id' => $catFood->id,
                'brand_id' => $brandWarmindo->id,
                'uom_id' => $uomPorsi->id,
                'name' => 'Nasi Ayam Geprek Sambal Bawang Jakal',
                'description' => 'Ayam krispi fillet digeprek sambal bawang pedas khas Kaliurang Jogja + nasi pulen hangat',
                'is_active' => true,
            ]
        );
        $vGeprekBawang = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-FNB-GPRK-BWG'],
            ['product_id' => $pGeprek->id, 'barcode' => 'FNB-GPK-01', 'name' => 'Nasi Ayam Geprek Sambal Bawang (Level 1-5)', 'weight_grams' => 350, 'base_price' => 17000.0, 'cost_price' => 9000.0]
        );
        $vGeprekKeju = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-FNB-GPRK-KJU'],
            ['product_id' => $pGeprek->id, 'barcode' => 'FNB-GPK-02', 'name' => 'Nasi Ayam Geprek Saus Keju Leleh', 'weight_grams' => 380, 'base_price' => 21000.0, 'cost_price' => 11500.0]
        );

        // 5. Nasi Orak-Arik Telur Sosis BBQ
        $pOrakArik = Product::firstOrCreate(
            ['code' => 'PRD-FNB-ORAKARIK'],
            [
                'category_id' => $catFood->id,
                'brand_id' => $brandWarmindo->id,
                'uom_id' => $uomPorsi->id,
                'name' => 'Nasi Orak-Arik Sosis & Bakso BBQ',
                'description' => 'Nasi hangat dengan topping orak-arik telur ayam, sosis, bakso tumis kecap pedas gurih',
                'is_active' => true,
            ]
        );
        $vOrakArik = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-FNB-ORK-SS'],
            ['product_id' => $pOrakArik->id, 'barcode' => 'FNB-ORK-01', 'name' => 'Nasi Orak-Arik Sosis & Bakso BBQ', 'weight_grams' => 300, 'base_price' => 15000.0, 'cost_price' => 7500.0]
        );

        // 6. Roti Bakar Cokelat Keju Susu (Roba)
        $pRotiBakar = Product::firstOrCreate(
            ['code' => 'PRD-FNB-ROTIBAKAR'],
            [
                'category_id' => $catFood->id,
                'brand_id' => $brandWarmindo->id,
                'uom_id' => $uomPorsi->id,
                'name' => 'Roti Bakar Cokelat Keju Susu (Roba)',
                'description' => 'Roti bakar mentega wangi isi meses cokelat pekat, keju parut gurih, dan krimer kental manis',
                'is_active' => true,
            ]
        );
        $vRotiBakar = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-FNB-ROBA-CK'],
            ['product_id' => $pRotiBakar->id, 'barcode' => 'FNB-RBA-01', 'name' => 'Roti Bakar Cokelat Keju Susu Kental Manis', 'weight_grams' => 200, 'base_price' => 13000.0, 'cost_price' => 6000.0]
        );

        // -------------------------------------------------------------
        // F&B: MINUMAN SEGAR & BOOSTER STAMINA GAMERS JOGJA
        // -------------------------------------------------------------
        // 7. Es Extra Joss Susu Dingin (Joshu Legend)
        $pJoshu = Product::firstOrCreate(
            ['code' => 'PRD-DRK-JOSHU'],
            [
                'category_id' => $catDrink->id,
                'brand_id' => $brandWarmindo->id,
                'uom_id' => $uomCup->id,
                'name' => 'Es Extra Joss Susu Dingin (Joshu Legend)',
                'description' => 'Minuman stamina legendaris gamers begadang: Extra Joss dingin dicampur krimer kental manis',
                'is_active' => true,
            ]
        );
        $vJoshu = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-DRK-JOSHU-CUP'],
            ['product_id' => $pJoshu->id, 'barcode' => 'DRK-JSH-01', 'name' => 'Es Extra Joss Susu Dingin (Joshu)', 'weight_grams' => 300, 'base_price' => 7000.0, 'cost_price' => 3500.0]
        );

        // 8. Es Kuku Bima Susu (Kususu Anggur)
        $pKususu = Product::firstOrCreate(
            ['code' => 'PRD-DRK-KUSUSU'],
            [
                'category_id' => $catDrink->id,
                'brand_id' => $brandWarmindo->id,
                'uom_id' => $uomCup->id,
                'name' => 'Es Kuku Bima Susu Anggur (Kususu)',
                'description' => 'Minuman energi rasa anggur manis segar dipadu susu kental manis dingin',
                'is_active' => true,
            ]
        );
        $vKususu = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-DRK-KUSUSU-CUP'],
            ['product_id' => $pKususu->id, 'barcode' => 'DRK-KSS-01', 'name' => 'Es Kuku Bima Susu Anggur Dingin', 'weight_grams' => 300, 'base_price' => 7000.0, 'cost_price' => 3500.0]
        );

        // 9. Es Kopi Susu Gula Aren MayaNet
        $pKopi = Product::firstOrCreate(
            ['code' => 'PRD-DRK-KOPI'],
            [
                'category_id' => $catDrink->id,
                'brand_id' => $brandWarmindo->id,
                'uom_id' => $uomCup->id,
                'name' => 'Es Kopi Susu Gula Aren MayaNet',
                'description' => 'Espresso Robusta blend Dampit Malang, susu segar, dan gula aren asli Jogja penahan kantuk',
                'is_active' => true,
            ]
        );
        $vKopiAren = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-DRK-KOPI-AREN'],
            ['product_id' => $pKopi->id, 'barcode' => 'FNB010', 'name' => 'Es Kopi Susu Aren MayaNet Jumbo 22oz', 'weight_grams' => 350, 'base_price' => 10000.0, 'cost_price' => 4500.0]
        );

        // 10. Kopi Tubruk Robusta Dampit (Panas)
        $pTubruk = Product::firstOrCreate(
            ['code' => 'PRD-DRK-TUBRUK'],
            [
                'category_id' => $catDrink->id,
                'brand_id' => $brandWarmindo->id,
                'uom_id' => $uomCup->id,
                'name' => 'Kopi Tubruk Robusta Dampit (Panas)',
                'description' => 'Kopi hitam seduh tubruk panas pekat beraroma karamel dan dark chocolate khas begadang malam',
                'is_active' => true,
            ]
        );
        $vTubruk = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-DRK-TUBRUK-HOT'],
            ['product_id' => $pTubruk->id, 'barcode' => 'DRK-TBR-01', 'name' => 'Kopi Tubruk Robusta Dampit Panas', 'weight_grams' => 200, 'base_price' => 6000.0, 'cost_price' => 2500.0]
        );

        // 11. Es Teh Manis Jumbo (Cup 22oz)
        $pTeh = Product::firstOrCreate(
            ['code' => 'PRD-DRK-TEH'],
            [
                'category_id' => $catDrink->id,
                'brand_id' => $brandWarmindo->id,
                'uom_id' => $uomCup->id,
                'name' => 'Es Teh Manis Melati Jumbo (22oz)',
                'description' => 'Es teh manis melati racikan khas Jogja ukuran jumbo penyejuk tenggorokan',
                'is_active' => true,
            ]
        );
        $vEsTeh = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-DRK-ESTEH'],
            ['product_id' => $pTeh->id, 'barcode' => 'FNB011', 'name' => 'Es Teh Manis Melati Jumbo (22oz)', 'weight_grams' => 350, 'base_price' => 5000.0, 'cost_price' => 1500.0]
        );

        // 12. Le Minerale 600ml Dingin
        $pMineral = Product::firstOrCreate(
            ['code' => 'PRD-DRK-MINERAL'],
            [
                'category_id' => $catDrink->id,
                'brand_id' => $brandMayora->id,
                'uom_id' => $uomBtl->id,
                'name' => 'Le Minerale 600ml Dingin',
                'description' => 'Air mineral pegunungan botol 600ml dingin segar dari kulkas showcase',
                'is_active' => true,
            ]
        );
        $vMineral = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-DRK-MINERAL-600'],
            ['product_id' => $pMineral->id, 'barcode' => 'FNB012', 'name' => 'Le Minerale 600ml Dingin', 'weight_grams' => 600, 'base_price' => 4000.0, 'cost_price' => 2200.0]
        );

        // 13. Good Day Freeze Mocafrio
        $pGoodDay = Product::firstOrCreate(
            ['code' => 'PRD-DRK-GOODDAY'],
            [
                'category_id' => $catDrink->id,
                'brand_id' => $brandMayora->id,
                'uom_id' => $uomCup->id,
                'name' => 'Es Good Day Freeze Mocafrio',
                'description' => 'Kopi dingin instan kocok rasa mocafrio menyegarkan dengan es batu kristal',
                'is_active' => true,
            ]
        );
        $vGoodDay = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-DRK-FREEZE-CUP'],
            ['product_id' => $pGoodDay->id, 'barcode' => 'DRK-GDY-01', 'name' => 'Es Good Day Freeze Mocafrio Cup', 'weight_grams' => 300, 'base_price' => 7000.0, 'cost_price' => 3500.0]
        );

        // -------------------------------------------------------------
        // F&B: CEMILAN & SNACK GAMERS
        // -------------------------------------------------------------
        // 14. Cireng Rujak Krispi
        $pCireng = Product::firstOrCreate(
            ['code' => 'PRD-SNK-CIRENG'],
            [
                'category_id' => $catSnack->id,
                'brand_id' => $brandWarmindo->id,
                'uom_id' => $uomPorsi->id,
                'name' => 'Cireng Rujak Krispi Hangat (10 pcs)',
                'description' => 'Cireng kenyal gurih digoreng renyah dengan cocolan bumbu rujak pedas manis asam',
                'is_active' => true,
            ]
        );
        $vCireng = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-SNK-CIRENG-10'],
            ['product_id' => $pCireng->id, 'barcode' => 'SNK-CRG-01', 'name' => 'Cireng Rujak Krispi (10 pcs)', 'weight_grams' => 250, 'base_price' => 12000.0, 'cost_price' => 5500.0]
        );

        // 15. Tahu Walik Krispi Jogja
        $pTahuWalik = Product::firstOrCreate(
            ['code' => 'PRD-SNK-TAHUWALIK'],
            [
                'category_id' => $catSnack->id,
                'brand_id' => $brandWarmindo->id,
                'uom_id' => $uomPorsi->id,
                'name' => 'Tahu Walik Krispi Khas Jogja (6 pcs)',
                'description' => 'Tahu walik isi adonan ayam kenyal gurih digoreng garing khas Jogja dengan sambal kecap',
                'is_active' => true,
            ]
        );
        $vTahuWalik = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-SNK-THW-6'],
            ['product_id' => $pTahuWalik->id, 'barcode' => 'SNK-THW-01', 'name' => 'Tahu Walik Krispi Khas Jogja (6 pcs)', 'weight_grams' => 220, 'base_price' => 12000.0, 'cost_price' => 6000.0]
        );

        // 16. French Fries Saus Keju & Sambal
        $pFries = Product::firstOrCreate(
            ['code' => 'PRD-SNK-FRIES'],
            [
                'category_id' => $catSnack->id,
                'brand_id' => $brandWarmindo->id,
                'uom_id' => $uomPorsi->id,
                'name' => 'French Fries Saus Keju & Sambal',
                'description' => 'Kentang goreng crinkle cut renyah disajikan hangat dengan siraman saus keju cheddar creamy',
                'is_active' => true,
            ]
        );
        $vFries = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-SNK-FRIES-KJ'],
            ['product_id' => $pFries->id, 'barcode' => 'SNK-FRS-01', 'name' => 'French Fries Saus Keju & Sambal', 'weight_grams' => 200, 'base_price' => 13000.0, 'cost_price' => 6500.0]
        );

        // 17. Sosis & Nugget Platter Gamers
        $pPlatter = Product::firstOrCreate(
            ['code' => 'PRD-SNK-PLATTER'],
            [
                'category_id' => $catSnack->id,
                'brand_id' => $brandWarmindo->id,
                'uom_id' => $uomPorsi->id,
                'name' => 'Sosis & Nugget Platter Gamers BBQ',
                'description' => 'Kombinasi sosis bratwurst mini dan chicken nugget krispi bumbu tabur BBQ',
                'is_active' => true,
            ]
        );
        $vPlatter = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-SNK-PLATTER-MIX'],
            ['product_id' => $pPlatter->id, 'barcode' => 'SNK-PLT-01', 'name' => 'Sosis & Nugget Platter Gamers BBQ', 'weight_grams' => 250, 'base_price' => 15000.0, 'cost_price' => 7500.0]
        );

        // 18. Chitato Sapi Panggang 68g
        $pChitato = Product::firstOrCreate(
            ['code' => 'PRD-SNK-CHITATO'],
            [
                'category_id' => $catSnack->id,
                'brand_id' => $brandIndofood->id,
                'uom_id' => $uomBks->id,
                'name' => 'Chitato Sapi Panggang 68g',
                'description' => 'Keripik kentang bergelombang rasa beef barbeque praktis saat ranked match',
                'is_active' => true,
            ]
        );
        $vChitato = ProductVariant::firstOrCreate(
            ['sku' => 'SKU-SNK-CHITATO-68G'],
            ['product_id' => $pChitato->id, 'barcode' => 'FNB020', 'name' => 'Chitato Sapi Panggang 68g', 'weight_grams' => 68, 'base_price' => 11000.0, 'cost_price' => 8200.0]
        );

        // 4. Stok Fisik Inventaris di Kasir & Gudang Dapur
        $posVariants = [
            $vDokdokOri->id => [40, 10],
            $vDokdokJumbo->id => [25, 5],
            $vIndomieTelur->id => [45, 10],
            $vIndomieKornet->id => [30, 8],
            $vMagelangan->id => [35, 10],
            $vGeprekBawang->id => [30, 8],
            $vGeprekKeju->id => [20, 5],
            $vOrakArik->id => [25, 5],
            $vRotiBakar->id => [20, 5],
            $vJoshu->id => [50, 15],
            $vKususu->id => [45, 15],
            $vKopiAren->id => [40, 10],
            $vTubruk->id => [35, 10],
            $vEsTeh->id => [60, 20],
            $vMineral->id => [60, 15],
            $vGoodDay->id => [40, 10],
            $vCireng->id => [25, 8],
            $vTahuWalik->id => [25, 8],
            $vFries->id => [30, 10],
            $vPlatter->id => [25, 8],
            $vChitato->id => [24, 5],
        ];

        foreach ($posVariants as $varId => [$onHand, $safety]) {
            StockInventory::updateOrCreate(
                ['warehouse_id' => $posWarehouse->id, 'product_variant_id' => $varId],
                ['qty_on_hand' => $onHand, 'safety_stock' => $safety]
            );
        }

        // Di Gudang Dapur / Pantry Pusat
        $kitchenVariants = [
            $vDokdokOri->id => [150, 40],
            $vDokdokJumbo->id => [100, 25],
            $vIndomieTelur->id => [200, 50],
            $vIndomieKornet->id => [120, 30],
            $vMagelangan->id => [120, 30],
            $vGeprekBawang->id => [100, 25],
            $vGeprekKeju->id => [80, 20],
            $vJoshu->id => [200, 50],
            $vKususu->id => [180, 40],
            $vKopiAren->id => [150, 35],
            $vTubruk->id => [120, 30],
            $vEsTeh->id => [250, 60],
            $vMineral->id => [300, 60],
            $vGoodDay->id => [150, 35],
            $vCireng->id => [100, 25],
            $vTahuWalik->id => [100, 25],
            $vFries->id => [120, 30],
            $vPlatter->id => [100, 25],
            $vChitato->id => [96, 20],
        ];

        foreach ($kitchenVariants as $varId => [$onHand, $safety]) {
            StockInventory::updateOrCreate(
                ['warehouse_id' => $kitchenWarehouse->id, 'product_variant_id' => $varId],
                ['qty_on_hand' => $onHand, 'safety_stock' => $safety]
            );
        }

        // 5. PC Stations (10 PC Reguler, 5 VIP Gaming)
        for ($i = 1; $i <= 10; $i++) {
            $code = sprintf('PC-%02d', $i);
            $isOccupied = in_array($i, [1, 2, 4, 7, 8]);
            PcStation::updateOrCreate(
                ['branch_id' => $branchWarnet->id, 'station_code' => $code],
                [
                    'station_type' => 'Reguler (Core i5 / GTX 1660 / 144Hz)',
                    'hourly_rate' => 6000.00,
                    'status' => $isOccupied ? 'occupied' : 'available',
                    'current_user_name' => $isOccupied ? "Member #{$i}0".rand(1, 9) : null,
                    'started_at' => $isOccupied ? now()->subMinutes(rand(20, 110)) : null,
                    'duration_minutes' => $isOccupied ? rand(120, 180) : 0,
                    'billing_amount' => $isOccupied ? 16000.00 : 0.00,
                ]
            );
        }

        for ($j = 1; $j <= 5; $j++) {
            $code = sprintf('VIP-%02d', $j);
            $isOccupied = in_array($j, [1, 3, 5]);
            PcStation::updateOrCreate(
                ['branch_id' => $branchWarnet->id, 'station_code' => $code],
                [
                    'station_type' => 'VIP Esports (Ryzen 7 / RTX 4070 / 240Hz)',
                    'hourly_rate' => 10000.00,
                    'status' => $isOccupied ? 'occupied' : 'available',
                    'current_user_name' => $isOccupied ? "VIP Gamers #{$j}" : null,
                    'started_at' => $isOccupied ? now()->subMinutes(rand(30, 90)) : null,
                    'duration_minutes' => $isOccupied ? 180 : 0,
                    'billing_amount' => $isOccupied ? 27000.00 : 0.00,
                ]
            );
        }

        // 6. Karyawan & Kasir Warnet
        $empRian = Employee::firstOrCreate(
            ['code' => 'OP-001'],
            [
                'branch_id' => $branchWarnet->id,
                'name' => 'Rian Pratama',
                'role' => EmployeeRole::KASIR,
                'max_discount_approval_percent' => 5.0,
                'phone' => '0812-4455-6677',
                'email' => 'rian.operator@cybercore.id',
                'is_active' => true,
            ]
        );

        $empDimas = Employee::firstOrCreate(
            ['code' => 'OP-002'],
            [
                'branch_id' => $branchWarnet->id,
                'name' => 'Dimas Anggara',
                'role' => EmployeeRole::KASIR,
                'max_discount_approval_percent' => 5.0,
                'phone' => '0813-7788-9900',
                'email' => 'dimas.operator@cybercore.id',
                'is_active' => true,
            ]
        );

        $empBayu = Employee::firstOrCreate(
            ['code' => 'OP-003'],
            [
                'branch_id' => $branchWarnet->id,
                'name' => 'Bayu Saputra',
                'role' => EmployeeRole::KASIR,
                'max_discount_approval_percent' => 5.0,
                'phone' => '0815-1122-3344',
                'email' => 'bayu.night@cybercore.id',
                'is_active' => true,
            ]
        );

        $empKevin = Employee::firstOrCreate(
            ['code' => 'TEK-001'],
            [
                'branch_id' => $branchWarnet->id,
                'name' => 'Kevin Sanjaya (Teknisi PC & Jaringan)',
                'username' => 'kevin',
                'position' => 'Teknisi',
                'jabatan' => 'hold',
                'content_permission' => 'all',
                'seniority' => 'middle',
                'evaluation' => 'Passed',
                'employment_status' => 'kontrak',
                'role' => EmployeeRole::OTORISATOR,
                'max_discount_approval_percent' => 15.0,
                'phone' => '0811-2233-4455',
                'email' => 'kevin.tech@cybercore.id',
                'is_active' => true,
            ]
        );

        // Karyawan ERP Maya Graha Kencana
        $mgkEmployees = [
            ['code' => 'EMP-001', 'name' => 'AAN', 'username' => 'Aan', 'email' => 'aqilanurhana29@gmail.com', 'position' => '--pilih--', 'jabatan' => 'sale', 'content_permission' => '--pi--', 'seniority' => 'none', 'evaluation' => 'none', 'employment_status' => '--pil--'],
            ['code' => 'EMP-002', 'name' => 'Abdul Aziz', 'username' => 'Abdul Aziz', 'email' => 'abdul.aziz@mayagrahakencana.com', 'position' => '--pilih--', 'jabatan' => 'mana', 'content_permission' => '--pi--', 'seniority' => 'none', 'evaluation' => 'none', 'employment_status' => '--pil--'],
            ['code' => 'EMP-003', 'name' => 'ADITYA', 'username' => 'aditya', 'email' => 'adityakurniawan89@gmail.com', 'position' => '--pilih--', 'jabatan' => 'sale', 'content_permission' => '--pi--', 'seniority' => 'none', 'evaluation' => 'none', 'employment_status' => '--pil--'],
            ['code' => 'EMP-004', 'name' => 'admin', 'username' => 'LT2', 'email' => 'admin@mayanet.com', 'position' => '--pilih--', 'jabatan' => 'hold', 'content_permission' => '--pi--', 'seniority' => 'none', 'evaluation' => 'none', 'employment_status' => '--pil--'],
            ['code' => 'EMP-005', 'name' => 'admin', 'username' => 'admin', 'email' => 'root@mayagrahakencana.com', 'position' => '--pilih--', 'jabatan' => 'hold', 'content_permission' => '--pi--', 'seniority' => 'none', 'evaluation' => 'none', 'employment_status' => '--pil--'],
            ['code' => 'EMP-006', 'name' => 'Aisyah', 'username' => 'Aisyah', 'email' => 'aisyah25icha@gmail.com', 'position' => '--pilih--', 'jabatan' => 'sale', 'content_permission' => '--pi--', 'seniority' => 'none', 'evaluation' => 'none', 'employment_status' => '--pil--'],
            ['code' => 'EMP-007', 'name' => 'ajeng', 'username' => 'ajeng', 'email' => 'ajengkurniawati76@gmail.com', 'position' => '--pilih--', 'jabatan' => 'sale', 'content_permission' => '--pi--', 'seniority' => 'none', 'evaluation' => 'none', 'employment_status' => '--pil--'],
            ['code' => 'EMP-008', 'name' => 'Akmal', 'username' => 'Akmal', 'email' => 'jonetsunosato00@gmail.com', 'position' => '--pilih--', 'jabatan' => 'sale', 'content_permission' => '--pi--', 'seniority' => 'none', 'evaluation' => 'none', 'employment_status' => '--pil--'],
            ['code' => 'EMP-009', 'name' => 'Aldi Rizandi', 'username' => 'Aldi Rizandi', 'email' => 'Aldirizandi75@gmail.com', 'position' => '--pilih--', 'jabatan' => 'sale', 'content_permission' => '--pi--', 'seniority' => 'none', 'evaluation' => 'none', 'employment_status' => '--pil--'],
            ['code' => 'EMP-010', 'name' => 'Alfredo Papa Rato', 'username' => 'Alfredo Papa Rato', 'email' => 'adifpapurato@gmail.com', 'position' => '--pilih--', 'jabatan' => 'sale', 'content_permission' => '--pi--', 'seniority' => 'none', 'evaluation' => 'none', 'employment_status' => '--pil--'],
            ['code' => 'EMP-011', 'name' => 'Ali', 'username' => 'Ali', 'email' => 'muhtaromali888@gmail.com', 'position' => '--pilih--', 'jabatan' => 'sale', 'content_permission' => '--pi--', 'seniority' => 'none', 'evaluation' => 'none', 'employment_status' => 'tetap'],
            ['code' => 'MGR-001', 'name' => 'Bagus', 'username' => 'Bagus', 'email' => 'bagus@mayagrahakencana.com', 'position' => 'Manager', 'jabatan' => 'mana', 'content_permission' => 'all', 'seniority' => 'senior', 'evaluation' => 'A', 'employment_status' => 'tetap'],
        ];

        foreach ($mgkEmployees as $mgk) {
            Employee::firstOrCreate(
                ['code' => $mgk['code']],
                array_merge($mgk, [
                    'branch_id' => $branchWarnet->id,
                    'role' => EmployeeRole::SALESMAN,
                    'max_discount_approval_percent' => 5.0,
                    'is_active' => true,
                ])
            );
        }

        // 7. Jadwal Shift Kerja Operator (Rotasi 4 Shift)
        $today = now()->toDateString();
        $empShift4 = Employee::where('code', 'EMP-011')->first() ?? $empKevin;

        // Shift 1: 01.00 - 07.00
        EmployeeSchedule::updateOrCreate(
            ['employee_id' => $empBayu->id, 'shift_date' => $today],
            [
                'branch_id' => $branchWarnet->id,
                'shift_name' => 'Shift 1 (01.00 - 07.00)',
                'start_time' => '01:00:00',
                'end_time' => '07:00:00',
                'status' => 'completed',
            ]
        );

        // Shift 2: 07.00 - 13.00
        EmployeeSchedule::updateOrCreate(
            ['employee_id' => $empRian->id, 'shift_date' => $today],
            [
                'branch_id' => $branchWarnet->id,
                'shift_name' => 'Shift 2 (07.00 - 13.00)',
                'start_time' => '07:00:00',
                'end_time' => '13:00:00',
                'status' => 'completed',
            ]
        );

        // Shift 3: 13.00 - 19.00
        EmployeeSchedule::updateOrCreate(
            ['employee_id' => $empDimas->id, 'shift_date' => $today],
            [
                'branch_id' => $branchWarnet->id,
                'shift_name' => 'Shift 3 (13.00 - 19.00)',
                'start_time' => '13:00:00',
                'end_time' => '19:00:00',
                'status' => 'scheduled',
            ]
        );

        // Shift 4: 19.00 - 01.00
        EmployeeSchedule::updateOrCreate(
            ['employee_id' => $empShift4->id, 'shift_date' => $today],
            [
                'branch_id' => $branchWarnet->id,
                'shift_name' => 'Shift 4 (19.00 - 01.00)',
                'start_time' => '19:00:00',
                'end_time' => '01:00:00',
                'status' => 'scheduled',
            ]
        );

        // 8. Presensi Hari Ini
        Attendance::updateOrCreate(
            ['employee_id' => $empRian->id, 'attendance_date' => $today],
            [
                'branch_id' => $branchWarnet->id,
                'check_in_at' => now()->startOfDay()->addHours(6)->addMinutes(54), // 06:54 tepat waktu sebelum shift 2 (07:00)
                'check_out_at' => now()->startOfDay()->addHours(13)->addMinutes(5), // 13:05 checkout selesai shift 2
                'status' => 'present',
                'late_minutes' => 0,
                'notes' => 'Hadir tepat waktu di Shift 2, serah terima laci kasir rapi.',
            ]
        );

        Attendance::updateOrCreate(
            ['employee_id' => $empDimas->id, 'attendance_date' => $today],
            [
                'branch_id' => $branchWarnet->id,
                'check_in_at' => now()->startOfDay()->addHours(13)->addMinutes(6), // 13:06 terlambat 6 mnt di shift 3 (13:00)
                'check_out_at' => null,
                'status' => 'late',
                'late_minutes' => 6,
                'notes' => 'Sedikit macet di jalan, langsung standby di meja kasir.',
            ]
        );

        // 9. Distribusi Stok (Transfer dari Dapur ke Etalase Kasir)
        StockTransferDispatch::updateOrCreate(
            ['transfer_code' => 'TRF-FNB-20261010-01'],
            [
                'from_warehouse_id' => $kitchenWarehouse->id,
                'to_warehouse_id' => $posWarehouse->id,
                'product_variant_id' => $vMineral->id,
                'qty' => 48,
                'status' => 'received',
                'dispatched_at' => now()->subHours(4),
                'received_at' => now()->subHours(3)->subMinutes(45),
                'operator_notes' => 'Refill 2 dus Le Minerale 600ml ke kulkas showcase kasir.',
            ]
        );

        StockTransferDispatch::updateOrCreate(
            ['transfer_code' => 'TRF-FNB-20261010-02'],
            [
                'from_warehouse_id' => $kitchenWarehouse->id,
                'to_warehouse_id' => $posWarehouse->id,
                'product_variant_id' => $vIndomieTelur->id,
                'qty' => 30,
                'status' => 'in_transit',
                'dispatched_at' => now()->subMinutes(15),
                'received_at' => null,
                'operator_notes' => 'Pengiriman 30 porsi mie instan dalam proses transfer.',
            ]
        );

        // 10. Transaksi Penjualan Warnet (Contoh Kasir POS)
        $sale1 = CafeSale::updateOrCreate(
            ['invoice_number' => 'INV-NET-20261010-001'],
            [
                'branch_id' => $branchWarnet->id,
                'employee_id' => $empRian->id,
                'customer_name' => 'Fajar Gamers (Member)',
                'pc_station_id' => 1,
                'total_billing' => 16000.00,
                'total_fnb' => 20000.00,
                'gross_amount' => 36000.00,
                'discount_amount' => 0.00,
                'tax_amount' => 0.00,
                'net_amount' => 36000.00,
                'amount_paid' => 50000.00,
                'change_amount' => 14000.00,
                'payment_reference' => 'QRIS-BCA-98124',
                'cashier_notes' => 'Pembayaran lunas via QRIS, kembalian tunai.',
                'payment_method' => 'qris',
                'payment_status' => 'paid',
            ]
        );

        CafePayment::updateOrCreate(
            ['payment_number' => 'PAY-20261010-001'],
            [
                'cafe_sale_id' => $sale1->id,
                'payment_method' => 'qris',
                'amount_due' => 36000.00,
                'amount_paid' => 50000.00,
                'change_amount' => 14000.00,
                'payment_reference' => 'QRIS-BCA-98124',
                'status' => 'completed',
                'paid_at' => now()->subHours(2),
                'cashier_employee_id' => $empRian->id,
            ]
        );

        CafeSaleItem::updateOrCreate(
            ['cafe_sale_id' => $sale1->id, 'item_name' => 'Paket Hemat Reguler 3 Jam'],
            ['product_variant_id' => $vBillReg3->id, 'item_type' => 'billing', 'qty' => 1, 'unit_price' => 16000.00, 'subtotal' => 16000.00]
        );
        CafeSaleItem::updateOrCreate(
            ['cafe_sale_id' => $sale1->id, 'item_name' => 'Indomie Goreng + Telur Setengah Matang'],
            ['product_variant_id' => $vIndomieTelur->id, 'item_type' => 'fnb', 'qty' => 1, 'unit_price' => 12000.00, 'subtotal' => 12000.00]
        );
        CafeSaleItem::updateOrCreate(
            ['cafe_sale_id' => $sale1->id, 'item_name' => 'Es Kopi Susu Aren Jumbo'],
            ['product_variant_id' => $vKopiAren->id, 'item_type' => 'fnb', 'qty' => 1, 'unit_price' => 8000.00, 'subtotal' => 8000.00]
        );

        // 11. Komisi Kasir / Operator Bulan Berjalan
        Commission::updateOrCreate(
            ['employee_id' => $empRian->id, 'period_month' => (int) now()->format('m'), 'period_year' => (int) now()->format('Y')],
            [
                'total_shift_hours' => 160,
                'total_fnb_sales' => 4500000.00,
                'commission_fnb_rate' => 5.00, // 5%
                'commission_fnb_amount' => 225000.00,
                'total_billing_sales' => 12800000.00,
                'commission_billing_rate' => 2.00, // 2%
                'commission_billing_amount' => 256000.00,
                'bonus_shift_amount' => 100000.00, // Bonus kedisiplinan tanpa telat
                'total_commission' => 581000.00,
                'status' => 'approved',
            ]
        );

        Commission::updateOrCreate(
            ['employee_id' => $empDimas->id, 'period_month' => (int) now()->format('m'), 'period_year' => (int) now()->format('Y')],
            [
                'total_shift_hours' => 152,
                'total_fnb_sales' => 3800000.00,
                'commission_fnb_rate' => 5.00,
                'commission_fnb_amount' => 190000.00,
                'total_billing_sales' => 10500000.00,
                'commission_billing_rate' => 2.00,
                'commission_billing_amount' => 210000.00,
                'bonus_shift_amount' => 50000.00,
                'total_commission' => 450000.00,
                'status' => 'approved',
            ]
        );

        $this->call(AttendanceMasterSeeder::class);
    }
}
