<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\IndonesiaRegionService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class IndonesiaRegionController extends Controller
{
    public function provinces(IndonesiaRegionService $regions): JsonResponse
    {
        try {
            return response()->json(['data' => $regions->provinces()]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => 'Data wilayah sementara tidak tersedia. Coba lagi nanti.'], 503);
        }
    }

    public function cities(string $provinceId, IndonesiaRegionService $regions): JsonResponse
    {
        try {
            abort_unless($regions->provinces()->contains(fn (array $province) => $province['id'] === $provinceId), 404);

            return response()->json(['data' => $regions->cities($provinceId)]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => 'Data kota/kabupaten sementara tidak tersedia. Coba lagi nanti.'], 503);
        }
    }
}
