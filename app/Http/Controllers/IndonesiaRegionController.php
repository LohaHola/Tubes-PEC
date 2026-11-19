<?php

namespace App\Http\Controllers;

use App\Services\IndonesiaRegionService;
use Illuminate\Http\JsonResponse;
use Throwable;

class IndonesiaRegionController extends Controller
{
    public function provinces(IndonesiaRegionService $regions): JsonResponse
    {
        try {
            return response()->json(['data' => $regions->provinces()]);
        } catch (Throwable $exception) {
            return response()->json(['message' => 'Data wilayah sementara tidak tersedia. Coba lagi nanti.'], 503);
        }
    }

    public function cities(string $provinceId, IndonesiaRegionService $regions): JsonResponse
    {
        try {
            $provinces = $regions->provinces();
        } catch (Throwable $exception) {
            return response()->json(['message' => 'Data wilayah sementara tidak tersedia. Coba lagi nanti.'], 503);
        }

        abort_unless($provinces->contains(fn (array $province) => $province['id'] === $provinceId), 404);

        try {
            return response()->json(['data' => $regions->cities($provinceId)]);
        } catch (Throwable $exception) {
            return response()->json(['message' => 'Data kota/kabupaten sementara tidak tersedia. Coba lagi nanti.'], 503);
        }
    }
}
