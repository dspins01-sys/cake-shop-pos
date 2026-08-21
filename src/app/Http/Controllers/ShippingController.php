<?php

namespace App\Http\Controllers;

use App\Services\RajaOngkirService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ShippingController extends Controller
{
    public function __construct(private RajaOngkirService $rajaOngkir) {}

    public function provinces(): JsonResponse
    {
        try {
            return response()->json(['data' => $this->rajaOngkir->provinces()]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function cities(int $provinceId): JsonResponse
    {
        try {
            return response()->json(['data' => $this->rajaOngkir->cities($provinceId)]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function districts(int $cityId): JsonResponse
    {
        try {
            return response()->json(['data' => $this->rajaOngkir->districts($cityId)]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function costs(Request $request): JsonResponse
    {
        $data = $request->validate([
            'destination_id' => 'required|integer',
            'weight' => 'required|integer|min:1',
            'courier' => 'required|string|max:30',
        ]);

        try {
            return response()->json([
                'data' => $this->rajaOngkir->costs(
                    $data['destination_id'],
                    $data['weight'],
                    $data['courier']
                ),
            ]);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
