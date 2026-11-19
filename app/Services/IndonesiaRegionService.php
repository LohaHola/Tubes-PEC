<?php

namespace App\\Services;

use Illuminate\\Support\\Collection;
use Illuminate\\Support\\Facades\\Cache;
use Illuminate\\Support\\Facades\\Http;
use RuntimeException;

class IndonesiaRegionService
{
    private const API_BASE = 'https://www.emsifa.com/api-wilayah-indonesia/api';

    public function provinces(): Collection
    {
        return Cache::remember('indonesia-regions.provinces', now()->addDays(7), function () {
            $response = Http::acceptJson()->timeout(8)->get(self::API_BASE . '/provinces.json');

            if (!$response->successful() || !is_array($response->json())) {
                throw new RuntimeException('Data provinsi tidak dapat dimuat.');
            }

            return collect($response->json())
                ->map(fn (array $item) => ['id' => (string) $item['id'], 'name' => (string) $item['name']])
                ->values();
        });
    }

    public function cities(string $provinceId): Collection
    {
        return Cache::remember('indonesia-regions.cities.' . $provinceId, now()->addDays(7), function () use ($provinceId) {
            $response = Http::acceptJson()->timeout(8)->get(self::API_BASE . '/regencies/' . rawurlencode($provinceId) . '.json');

            if (!$response->successful() || !is_array($response->json())) {
                throw new RuntimeException('Data kota/kabupaten tidak dapat dimuat.');
            }

            return collect($response->json())
                ->map(fn (array $item) => ['id' => (string) $item['id'], 'name' => (string) $item['name']])
                ->values();
        });
    }

    public function findProvinceByName(?string $name): ?array
    {
        if (!$name) {
            return null;
        }

        return $this->provinces()->first(
            fn (array $province) => mb_strtolower($province['name']) === mb_strtolower(trim($name))
        );
    }

    public function findCity(string $provinceId, ?string $name): ?array
    {
        if (!$name) {
            return null;
        }

        return $this->cities($provinceId)->first(
            fn (array $city) => mb_strtolower($city['name']) === mb_strtolower(trim($name))
        );
    }
}
