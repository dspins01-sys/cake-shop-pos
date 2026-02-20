<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NodeWhatsAppService
{
    protected $baseUrl;
    protected $timeout;

    public function __construct()
    {
        $this->baseUrl = env('NODE_WA_URL', 'http://wa-service:3000');
        $this->timeout = 30;
    }

    /**
     * Check service health
     */
    public function health()
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl . '/health');
            return $response->json();
        } catch (\Exception $e) {
            Log::error('WA Health check failed: ' . $e->getMessage());
            return ['status' => 'error'];
        }
    }

    /**
     * Send WhatsApp message
     */
    public function send($phone, $message)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/send', [
                    'phone' => $this->formatPhone($phone),
                    'message' => $message
                ]);

            if ($response->successful()) {
                Log::info("WA sent to {$phone}");
                return true;
            }

            Log::error('WA send failed: ' . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('WA Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send bulk messages
     */
    public function sendBulk(array $messages)
    {
        try {
            $response = Http::timeout($this->timeout * 2)
                ->post($this->baseUrl . '/send-bulk', [
                    'recipients' => $messages
                ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('WA Bulk error: ' . $e->getMessage());
            return ['results' => []];
        }
    }

    /**
     * Get QR code (buat ditampilkan)
     */
    public function getQR()
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl . '/qr');
            return $response->json();
        } catch (\Exception $e) {
            return ['qr' => null, 'status' => 'error'];
        }
    }

    /**
     * Format nomor HP
     */
    protected function formatPhone($phone)
    {
        // Hapus semua non-digit
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Ubah 0 jadi 62
        if (substr($phone, 0, 1) == '0') {
            $phone = '62' . substr($phone, 1);
        }
        
        return $phone;
    }
}