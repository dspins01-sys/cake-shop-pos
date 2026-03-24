<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use App\Services\NodeWhatsAppService;
use Illuminate\Support\Facades\Log;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired';
    protected $description = 'Cancel orders that have expired payment time';

    public function handle()
    {
        $this->info('Checking expired orders...');
        
        // Log waktu sekarang
        $now = now();
        $this->info("Current time: " . $now);
        
        // Cari order expired
        $expiredOrders = Order::where('expired_at', '<', $now)
            ->where('status', 'pending')
            ->where('payment_status', 'unpaid')
            ->get();
        
        $this->info("Found " . $expiredOrders->count() . " expired orders");
        
        $whatsapp = app(NodeWhatsAppService::class);
        $count = 0;
        
        foreach ($expiredOrders as $order) {
            $this->info("Processing order: {$order->order_number}");
            
            // Update status
            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'expired'
            ]);
            
            // Kirim WA
            $message = "❌ *PESANAN KADALUARSA*\n\n";
            $message .= "Halo *{$order->customer_name}*,\n";
            $message .= "Pesanan Anda telah dibatalkan karena melebihi batas waktu pembayaran (24 jam).\n\n";
            $message .= "📋 *DETAIL PESANAN*\n";
            $message .= "No. Order: {$order->order_number}\n";
            $message .= "Total: Rp " . number_format($order->total, 0, ',', '.') . "\n\n";
            $message .= "Silakan lakukan pemesanan ulang jika masih ingin membeli.\n";
            $message .= "Terima kasih! 🙏";
            
            try {
                $whatsapp->send($order->customer_phone, $message);
                $this->info("WA sent to {$order->customer_phone}");
                Log::info("Expired order cancelled and WA sent", ['order' => $order->id]);
            } catch (\Exception $e) {
                $this->error("Failed to send WA: " . $e->getMessage());
                Log::error("Failed to send expired WA", ['order' => $order->id, 'error' => $e->getMessage()]);
            }
            
            $count++;
        }
        
        $this->info("{$count} expired orders have been cancelled.");
        
        return Command::SUCCESS;
    }
}