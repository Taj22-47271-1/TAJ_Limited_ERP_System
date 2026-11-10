<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Online Orders performance index
        if (Schema::hasTable('online_orders')) {
            Schema::table('online_orders', function (Blueprint $table) {
                try {
                    $table->index('user_id', 'idx_online_orders_user_id');
                } catch (\Throwable) {}
            });
        }

        // 2. Sales performance index
        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) {
                try {
                    $table->index('created_by', 'idx_sales_created_by');
                } catch (\Throwable) {}
                try {
                    $table->index('online_order_id', 'idx_sales_online_order_id');
                } catch (\Throwable) {}
            });
        }

        // 3. Purchases performance index
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                try {
                    $table->index('purchase_date', 'idx_purchases_purchase_date');
                } catch (\Throwable) {}
            });
        }

        // 4. Products performance index
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                try {
                    $table->index('status', 'idx_products_status');
                } catch (\Throwable) {}
                try {
                    $table->index('category_id', 'idx_products_category_id');
                } catch (\Throwable) {}
            });
        }
    }

    public function down(): void
    {
        // Non-destructive rollback
    }
};
