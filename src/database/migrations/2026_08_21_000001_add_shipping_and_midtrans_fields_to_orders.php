<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('shipping_cost')->default(0)->after('total');
            $table->unsignedInteger('shipping_weight')->default(0)->after('shipping_cost');
            $table->unsignedBigInteger('shipping_destination_id')->nullable()->after('shipping_weight');
            $table->string('shipping_province')->nullable()->after('shipping_destination_id');
            $table->string('shipping_city')->nullable()->after('shipping_province');
            $table->string('shipping_district')->nullable()->after('shipping_city');
            $table->string('courier')->nullable()->after('shipping_district');
            $table->string('courier_service')->nullable()->after('courier');
            $table->string('shipping_etd')->nullable()->after('courier_service');
            $table->string('midtrans_token')->nullable()->after('payment_status');
            $table->string('midtrans_transaction_id')->nullable()->after('midtrans_token');
            $table->string('midtrans_payment_type')->nullable()->after('midtrans_transaction_id');
            $table->string('midtrans_status')->nullable()->after('midtrans_payment_type');
            $table->timestamp('midtrans_paid_at')->nullable()->after('midtrans_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_cost', 'shipping_weight', 'shipping_destination_id',
                'shipping_province', 'shipping_city', 'shipping_district',
                'courier', 'courier_service', 'shipping_etd',
                'midtrans_token', 'midtrans_transaction_id', 'midtrans_payment_type',
                'midtrans_status', 'midtrans_paid_at',
            ]);
        });
    }
};
