<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add discount and colorways to products
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (! Schema::hasColumn('products', 'discount_price')) {
                    $table->decimal('discount_price', 15, 2)->nullable()->after('selling_price');
                }
                if (! Schema::hasColumn('products', 'discount_percent')) {
                    $table->decimal('discount_percent', 5, 2)->nullable()->after('discount_price');
                }
                if (! Schema::hasColumn('products', 'colorways')) {
                    $table->json('colorways')->nullable()->after('showcase_config');
                }
            });
        }

        // 2. Add selected_color to online_order_items
        if (Schema::hasTable('online_order_items')) {
            Schema::table('online_order_items', function (Blueprint $table) {
                if (! Schema::hasColumn('online_order_items', 'selected_color')) {
                    $table->string('selected_color')->nullable()->after('selected_size');
                }
            });
        }

        // 3. Add selected_color to sale_items
        if (Schema::hasTable('sale_items')) {
            Schema::table('sale_items', function (Blueprint $table) {
                if (! Schema::hasColumn('sale_items', 'selected_color')) {
                    $table->string('selected_color')->nullable()->after('selected_size');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'discount_price')) {
                    $table->dropColumn('discount_price');
                }
                if (Schema::hasColumn('products', 'discount_percent')) {
                    $table->dropColumn('discount_percent');
                }
                if (Schema::hasColumn('products', 'colorways')) {
                    $table->dropColumn('colorways');
                }
            });
        }

        if (Schema::hasTable('online_order_items') && Schema::hasColumn('online_order_items', 'selected_color')) {
            Schema::table('online_order_items', function (Blueprint $table) {
                $table->dropColumn('selected_color');
            });
        }

        if (Schema::hasTable('sale_items') && Schema::hasColumn('sale_items', 'selected_color')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->dropColumn('selected_color');
            });
        }
    }
};
