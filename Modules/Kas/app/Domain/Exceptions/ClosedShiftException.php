<?php

declare(strict_types=1);

namespace Modules\Kas\Domain\Exceptions;

use RuntimeException;

class ClosedShiftException extends RuntimeException
{
    public function __construct(
        string $message = 'Sesi laci kasir telah ditutup. Transaksi kasir tidak dapat dilakukan sebelum shift dibuka kembali.'
    ) {
        parent::__construct($message);
    }
}
