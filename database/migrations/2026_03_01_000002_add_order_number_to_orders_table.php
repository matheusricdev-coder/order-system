<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('order_number')->nullable()->after('id');
        });

        // Populate sequential numbers for existing orders (ordered by created_at)
        $orders = DB::table('orders')->orderBy('created_at')->get(['id']);
        foreach ($orders as $index => $order) {
            DB::table('orders')
                ->where('id', $order->id)
                ->update(['order_number' => $index + 1]);
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('order_number')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['order_number']);
            $table->dropColumn('order_number');
        });
    }
};
