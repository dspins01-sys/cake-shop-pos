<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function generate(Order $order)
    {
        $data = [
            'order' => $order,
            'items' => $order->items,
            'company' => [
                'name' => 'SweetCake',
                'address' => 'Jl. Contoh No. 123',
                'phone' => '+62 812 3456 7890',
                'email' => 'info@sweetcake.com',
            ]
        ];

        $pdf = PDF::loadView('invoices.template', $data);
        
        return $pdf->download('invoice-'.$order->order_number.'.pdf');
    }

    public function view(Order $order)
    {
        $data = [
            'order' => $order,
            'items' => $order->items,
            'company' => [
                'name' => 'SweetCake',
                'address' => 'Jl. Contoh No. 123',
                'phone' => '+62 812 3456 7890',
                'email' => 'info@sweetcake.com',
            ]
        ];

        return view('invoices.template', $data);
    }
}