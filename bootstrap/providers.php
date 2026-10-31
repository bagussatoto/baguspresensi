<?php

use App\Providers\AppServiceProvider;
use Modules\Distribusi\Providers\DistribusiServiceProvider;
use Modules\Kas\Providers\KasServiceProvider;
use Modules\MasterData\Providers\MasterDataServiceProvider;
use Modules\Pembelian\Providers\PembelianServiceProvider;
use Modules\Penjualan\Providers\PenjualanServiceProvider;

return [
    AppServiceProvider::class,
    MasterDataServiceProvider::class,
    KasServiceProvider::class,
    PembelianServiceProvider::class,
    DistribusiServiceProvider::class,
    PenjualanServiceProvider::class,
];
