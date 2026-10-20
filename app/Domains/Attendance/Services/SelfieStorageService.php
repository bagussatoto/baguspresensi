<?php

declare(strict_types=1);

namespace App\Domains\Attendance\Services;

use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Service untuk menangani penyimpanan, validasi, dan proteksi foto selfie presensi.
 */
class SelfieStorageService
{
    /**
     * Direktori dasar penyimpanan foto selfie presensi di folder public.
     */
    protected string $basePath;

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?? public_path('uploads/selfies');
    }

    /**
     * Simpan file foto selfie (mendukung UploadedFile maupun Base64 string dari kamera browser).
     *
     * @param  UploadedFile|string  $photo  File unggahan atau string base64
     * @param  int  $employeeId  ID Karyawan
     * @param  string  $type  'clock_in' atau 'clock_out'
     * @return string Path relatif file yang tersimpan
     *
     * @throws DomainException
     */
    public function storeSelfie(UploadedFile|string $photo, int $employeeId, string $type = 'clock_in'): string
    {
        $year = date('Y');
        $month = date('m');
        $subDir = "{$year}/{$month}";
        $targetDirectory = "{$this->basePath}/{$subDir}";

        if (! File::isDirectory($targetDirectory)) {
            File::makeDirectory($targetDirectory, 0755, true, true);
        }

        $timestamp = time();
        $random = Str::random(8);

        if ($photo instanceof UploadedFile) {
            $extension = strtolower($photo->getClientOriginalExtension());
            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $extension = 'jpg';
            }

            $fileName = "{$type}_{$employeeId}_{$timestamp}_{$random}.{$extension}";
            $photo->move($targetDirectory, $fileName);

            return "uploads/selfies/{$subDir}/{$fileName}";
        }

        // Penanganan format Base64 (data:image/jpeg;base64,...)
        if (is_string($photo)) {
            $photoData = $photo;
            $extension = 'jpg';

            if (str_contains($photoData, ';base64,')) {
                $parts = explode(';base64,', $photoData);
                if (str_contains($parts[0], 'image/png')) {
                    $extension = 'png';
                } elseif (str_contains($parts[0], 'image/webp')) {
                    $extension = 'webp';
                }
                $photoData = $parts[1];
            }

            $decoded = base64_decode($photoData, true);
            if ($decoded === false || strlen($decoded) < 20) {
                throw new DomainException('Format foto selfie base64 tidak valid atau rusak.');
            }

            $fileName = "{$type}_{$employeeId}_{$timestamp}_{$random}.{$extension}";
            $filePath = "{$targetDirectory}/{$fileName}";
            File::put($filePath, $decoded);

            return "uploads/selfies/{$subDir}/{$fileName}";
        }

        throw new DomainException('Tipe data foto selfie tidak didukung.');
    }

    /**
     * Dapatkan URL publik file foto selfie.
     */
    public function getUrl(?string $relativePath): ?string
    {
        if ($relativePath === null || $relativePath === '') {
            return null;
        }

        return asset($relativePath);
    }

    /**
     * Hapus file foto selfie dari storage.
     */
    public function deleteSelfie(?string $relativePath): bool
    {
        if ($relativePath === null || $relativePath === '') {
            return false;
        }

        $fullPath = public_path($relativePath);
        if (File::exists($fullPath)) {
            return File::delete($fullPath);
        }

        return false;
    }
}
