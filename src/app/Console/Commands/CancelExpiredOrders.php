<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\NodeWhatsAppService;
use Illuminate\Console\Command;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired';
    protected $description = 'Cancel orders that have expired payment time';

    protected $whatsapp;

    public function __construct(NodeWhatsAppService $whatsapp)
    {
        parent::__construct();
        $this->whatsapp = $whatsapp;
    }

    public function handle()
    {
        $this->info('Checking expired orders...');

        // Cari order yang expired (pending/waiting_confirmation dan lewat expired_at)
        $expiredOrders = Order::whereIn('status', ['pending', 'waiting_confirmation'])
            ->where('expired_at', '<', now())
            ->where('payment_status', '!=', 'paid')
            ->get();

        $count = 0;
        foreach ($expiredOrders as $order) {
            // Update status
            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'expired'
            ]);

            // Kirim WA ke customer (optional)
            $message = "⏰ *PESANAN EXPIRED*\n\n";
            $message .= "Halo *{$order->customer_name}*,\n";
            $message .= "Pesanan #{$order->order_number} telah dibatalkan otomatis karena melebihi batas waktu pembayaran (24 jam).\n\n";
            $message .= "Silakan lakukan order ulang jika masih ingin berbelanja.\n";
            $message .= "Terima kasih! 🙏";

            $this->whatsapp->send($order->customer_phone, $message);

            $count++;
        }

        $this->info("{$count} expired orders have been cancelled.");
    }
}
