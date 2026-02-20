<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * View invoice di browser
     */
    public function view(Order $order)
    {
        // Cek akses (hanya admin atau pemilik order)
        if (auth()->user()->role !== 'admin' && auth()->user()->email !== $order->customer_email) {
            abort(403, 'Unauthorized');
        }

        $data = [
            'order' => $order,
            'items' => $order->items,
            'company' => [
                'name' => 'SweetCake Bakery',
                'address' => 'Jl. Contoh No. 123, Jakarta',
                'phone' => '+62 812 3456 7890',
                'email' => 'info@sweetcake.com',
                'website' => 'www.sweetcake.com'
            ]
        ];

        return view('invoices.template', $data);
    }

    /**
     * Download invoice sebagai PDF
     */
    public function download(Order $order)
    {
        // Cek akses (hanya admin atau pemilik order)
        if (auth()->user()->role !== 'admin' && auth()->user()->email !== $order->customer_email) {
            abort(403, 'Unauthorized');
        }

        $data = [
            'order' => $order,
            'items' => $order->items,
            'company' => [
                'name' => 'SweetCake Bakery',
                'address' => 'Jl. Contoh No. 123, Jakarta',
                'phone' => '+62 812 3456 7890',
                'email' => 'info@sweetcake.com',
                'website' => 'www.sweetcake.com'
            ]
        ];

        $pdf = Pdf::loadView('invoices.template', $data);
        
        return $pdf->download('invoice-'.$order->order_number.'.pdf');
    }

    /**
     * Print invoice langsung (untuk pengiriman)
     */
    public function print(Order $order)
    {
        // Cek akses (hanya admin)
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $data = [
            'order' => $order,
            'items' => $order->items,
            'company' => [
                'name' => 'SweetCake Bakery',
                'address' => 'Jl. Contoh No. 123, Jakarta',
                'phone' => '+62 812 3456 7890',
                'email' => 'info@sweetcake.com',
                'website' => 'www.sweetcake.com'
            ]
        ];

        return view('invoices.print', $data);
    }
}