<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class RajaOngkirService
{
    private function client()
    {
        $key = config('payment.rajaongkir.api_key');
        if (!$key) {
            throw new RuntimeException('RAJAONGKIR_API_KEY is not configured.');
        }

        return Http::withHeaders([
            'key' => $key,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->baseUrl(rtrim(config('payment.rajaongkir.base_url'), '/'));
    }

    public function provinces(): array
    {
        return $this->get('/destination/province')['data'] ?? [];
    }

    public function cities(int $provinceId): array
    {
        return $this->get('/destination/city/' . $provinceId)['data'] ?? [];
    }

    public function districts(int $cityId): array
    {
        return $this->get('/destination/district/' . $cityId)['data'] ?? [];
    }

    public function costs(int $destinationId, int $weightGram, string $courier): array
    {
        $originId = config('payment.rajaongkir.origin_id');
        if (!$originId) {
            throw new RuntimeException('RAJAONGKIR_ORIGIN_ID is not configured.');
        }

        $response = $this->client()->asForm()->post('/calculate/domestic-cost', [
            'origin' => $originId,
            'destination' => $destinationId,
            'weight' => max(1, $weightGram),
            'courier' => strtolower($courier),
            'price' => 'lowest',
        ]);

        if ($response->failed()) {
            throw new RuntimeException('RajaOngkir error: ' . $response->body());
        }

        return $response->json('data') ?? [];
    }

    public function findQuote(int $destinationId, int $weightGram, string $courier, string $service): ?array
    {
        foreach ($this->costs($destinationId, $weightGram, $courier) as $quote) {
            if (($quote['service'] ?? '') === $service) {
                return $quote;
            }
        }

        return null;
    }

    private function get(string $path): array
    {
        $response = $this->client()->get($path);
        if ($response->failed()) {
            throw new RuntimeException('RajaOngkir error: ' . $response->body());
        }

        return $response->json() ?? [];
    }
}
