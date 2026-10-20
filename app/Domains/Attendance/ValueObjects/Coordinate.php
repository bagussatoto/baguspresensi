<?php

declare(strict_types=1);

namespace App\Domains\Attendance\ValueObjects;

use App\Domains\Attendance\Exceptions\InvalidCoordinateException;

/**
 * Value Object Imutabel: Koordinat Geografis (Latitude & Longitude)
 * Dilengkapi formula Haversine untuk kalkulasi jarak fisik dalam satuan meter.
 */
readonly class Coordinate
{
    public const EARTH_RADIUS_METERS = 6371000.0;

    public float $latitude;

    public float $longitude;

    public function __construct(float $latitude, float $longitude)
    {
        if ($latitude < -90.0 || $latitude > 90.0) {
            throw new InvalidCoordinateException(
                "Latitude {$latitude} di luar rentang valid (-90.0 s/d 90.0)."
            );
        }

        if ($longitude < -180.0 || $longitude > 180.0) {
            throw new InvalidCoordinateException(
                "Longitude {$longitude} di luar rentang valid (-180.0 s/d 180.0)."
            );
        }

        $this->latitude = round($latitude, 8);
        $this->longitude = round($longitude, 8);
    }

    /**
     * Hitung jarak lurus (Great-circle distance) ke koordinat lain menggunakan Formula Haversine.
     * Mengembalikan jarak dalam satuan meter (dibulatkan 2 desimal).
     */
    public function distanceTo(self $other): float
    {
        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($other->latitude);
        $lonTo = deg2rad($other->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(
            sqrt(
                sin($latDelta / 2) ** 2 +
                cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2
            )
        );

        return round($angle * self::EARTH_RADIUS_METERS, 2);
    }

    /**
     * Konversi ke representasi array asosiatif.
     *
     * @return array{latitude: float, longitude: float}
     */
    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    /**
     * Representasi string sederhana "latitude,longitude".
     */
    public function __toString(): string
    {
        return "{$this->latitude},{$this->longitude}";
    }
}
