<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\MasterData\Domain\Models\Brand;

class AdminBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            // Kolom 1 (1 - 36)
            ['name' => '3G', 'code' => null],
            ['name' => 'ABC', 'code' => null],
            ['name' => 'Advance', 'code' => null],
            ['name' => 'Angel', 'code' => null],
            ['name' => 'AOKA', 'code' => 'RA'],
            ['name' => 'Apogee', 'code' => null],
            ['name' => 'Apollyon', 'code' => null],
            ['name' => 'AQUA', 'code' => null],
            ['name' => 'Art', 'code' => null],
            ['name' => 'AVI', 'code' => null],
            ['name' => 'AX', 'code' => null],
            ['name' => 'Bluetech', 'code' => null],
            ['name' => 'BuffTech', 'code' => null],
            ['name' => 'Bushnell', 'code' => null],
            ['name' => 'Canon', 'code' => null],
            ['name' => 'Casio', 'code' => null],
            ['name' => 'Cobra', 'code' => null],
            ['name' => 'Dakey1', 'code' => null],
            ['name' => 'Dell', 'code' => null],
            ['name' => 'D-Link', 'code' => null],
            ['name' => 'E-Blue', 'code' => null],
            ['name' => 'Elephant', 'code' => null],
            ['name' => 'Epson', 'code' => null],
            ['name' => 'Fantech', 'code' => null],
            ['name' => 'Fleco', 'code' => null],
            ['name' => 'Genius', 'code' => null],
            ['name' => 'Gembird', 'code' => null],
            ['name' => 'Gigabyte', 'code' => null],
            ['name' => 'Google', 'code' => null],
            ['name' => 'Graphtec', 'code' => null],
            ['name' => 'Havit', 'code' => null],
            ['name' => 'HP', 'code' => null],
            ['name' => 'Havit Pro', 'code' => null],
            ['name' => 'Hikvision', 'code' => null],
            ['name' => 'Hitachi', 'code' => null],
            ['name' => 'Havit Gaming', 'code' => null],

            // Kolom 2 (37 - 72)
            ['name' => 'Huawei', 'code' => null],
            ['name' => 'Hunter', 'code' => null],
            ['name' => 'HY-E', 'code' => null],
            ['name' => 'i-Tech', 'code' => null],
            ['name' => 'Iwuz', 'code' => null],
            ['name' => 'ISOTONIK', 'code' => 'ISOTONIK'],
            ['name' => 'K-One', 'code' => null],
            ['name' => 'Keenion', 'code' => null],
            ['name' => 'Kenko', 'code' => null],
            ['name' => 'Kingston', 'code' => null],
            ['name' => 'Kodak', 'code' => null],
            ['name' => 'Kokai', 'code' => null],
            ['name' => 'LCL', 'code' => null],
            ['name' => 'Lexar', 'code' => null],
            ['name' => 'Logitech', 'code' => null],
            ['name' => 'Love', 'code' => null],
            ['name' => 'M-Tech', 'code' => null],
            ['name' => 'Maike', 'code' => null],
            ['name' => 'Marvo', 'code' => null],
            ['name' => 'Mediatech', 'code' => null],
            ['name' => 'Microsoft', 'code' => null],
            ['name' => 'Mikrotik', 'code' => null],
            ['name' => 'MSI', 'code' => null],
            ['name' => 'Morro', 'code' => null],
            ['name' => 'Nemesis', 'code' => null],
            ['name' => 'NYK', 'code' => null],
            ['name' => 'OKI', 'code' => null],
            ['name' => 'Orico', 'code' => null],
            ['name' => 'Panasonic', 'code' => null],
            ['name' => 'Philips', 'code' => null],
            ['name' => 'Pioneer', 'code' => null],
            ['name' => 'Plextor', 'code' => null],
            ['name' => 'Powerlogic', 'code' => null],
            ['name' => 'Prolink', 'code' => null],
            ['name' => 'Promate', 'code' => null],
            ['name' => 'Predator', 'code' => null],

            // Kolom 3 (73 - 108)
            ['name' => 'Presenter', 'code' => null],
            ['name' => 'ProTouch', 'code' => null],
            ['name' => 'Rambo', 'code' => null],
            ['name' => 'Red Bridge', 'code' => null],
            ['name' => 'S-Pro', 'code' => null],
            ['name' => 'Sandisk', 'code' => null],
            ['name' => 'SAMPOERNA', 'code' => null],
            ['name' => 'SanDisk Ultra', 'code' => null],
            ['name' => 'Sanyo', 'code' => null],
            ['name' => 'SD', 'code' => null],
            ['name' => 'Siyuteam', 'code' => null],
            ['name' => 'Smartfren', 'code' => null],
            ['name' => 'Sony', 'code' => null],
            ['name' => 'Spectra', 'code' => null],
            ['name' => 'Storm', 'code' => null],
            ['name' => 'Sturdy', 'code' => null],
            ['name' => 'Sunglasses', 'code' => null],
            ['name' => 'Supreme', 'code' => null],
            ['name' => 'Targus', 'code' => null],
            ['name' => 'Tenda', 'code' => null],
            ['name' => 'Thermaltake', 'code' => null],
            ['name' => 'Toshiba', 'code' => null],
            ['name' => 'TP-Link', 'code' => null],
            ['name' => 'Transcend', 'code' => null],
            ['name' => 'Ugreen', 'code' => null],
            ['name' => 'Unitek', 'code' => null],
            ['name' => 'V-Gen', 'code' => null],
            ['name' => 'ViewSonic', 'code' => null],
            ['name' => 'Vivan', 'code' => null],
            ['name' => 'Wacom', 'code' => null],
            ['name' => 'Western Digital', 'code' => null],
            ['name' => 'Xiaomi', 'code' => null],
            ['name' => 'Xigmatek', 'code' => null],
            ['name' => 'Zalman', 'code' => null],
            ['name' => 'ZOTAC', 'code' => null],
            ['name' => 'Zyrex', 'code' => null],
        ];

        foreach ($brands as $index => $item) {
            $baseSlug = Str::slug($item['name']);
            if (empty($baseSlug)) {
                $baseSlug = 'brand-'.$index;
            }
            $slug = $baseSlug;
            $counter = 1;
            while (Brand::where('slug', $slug)->exists()) {
                $existing = Brand::where('slug', $slug)->first();
                if ($existing && strtolower($existing->name) === strtolower($item['name'])) {
                    break;
                }
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            Brand::firstOrCreate(
                ['name' => $item['name']],
                [
                    'code' => $item['code'],
                    'slug' => $slug,
                    'is_active' => true,
                ]
            );
        }
    }
}
