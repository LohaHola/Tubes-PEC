<?php

namespace App\Services;

use Illuminate\Support\Collection;
use RuntimeException;
use Throwable;

class IndonesiaRegionService
{
    public function provinces(): Collection
    {
        return collect($this->readDataset('indonesia-provinces.json'))
            ->map(fn (array $item) => [
                'id' => (string) $item['id'],
                'name' => (string) $item['name'],
            ])
            ->values();
    }

    public function cities(string $provinceId): Collection
    {
        return collect($this->readDataset('indonesia-regencies.json'))
            ->filter(fn (array $item) => (string) ($item['provinceId'] ?? '') === $provinceId)
            ->map(fn (array $item) => [
                'id' => (string) $item['id'],
                'name' => (string) $item['name'],
            ])
            ->values();
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

    /**
     * Read bundled JSON data so address forms do not depend on a third-party API.
     *
     * @return array<int, array<string, mixed>>
     */
    private function readDataset(string $filename): array
    {
        $path = resource_path('data/' . $filename);

        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException('File data wilayah tidak ditemukan: ' . $filename);
        }

        try {
            $items = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            throw new RuntimeException('File data wilayah tidak dapat dibaca: ' . $filename, previous: $exception);
        }

        if (!is_array($items)) {
            throw new RuntimeException('Format file data wilayah tidak valid: ' . $filename);
        }

        return $items;
    }
}
