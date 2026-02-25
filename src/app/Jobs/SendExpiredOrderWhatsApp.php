<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\NodeWhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendExpiredOrderWhatsApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;

    public $tries = 3;        // retry 3x
    public $timeout = 20;     // max 20 detik

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(NodeWhatsAppService $whatsapp)
    {
        $message = "⏰ *PESANAN EXPIRED*\n\n";
        $message .= "Halo *{$this->order->customer_name}*,\n";
        $message .= "Pesanan #{$this->order->order_number} telah dibatalkan otomatis karena melebihi batas waktu pembayaran (24 jam).\n\n";
        $message .= "Silakan lakukan order ulang jika masih ingin berbelanja.\n";
        $message .= "Terima kasih! 🙏";

        $whatsapp->send($this->order->customer_phone, $message);

        Log::info("Expired WA queued for order {$this->order->id}");
    }
}
