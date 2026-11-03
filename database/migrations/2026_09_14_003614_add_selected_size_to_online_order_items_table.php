<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('online_order_items') && !Schema::hasColumn('online_order_items', 'selected_size')) {
            Schema::table('online_order_items', function (Blueprint $table) {
                $table->string('selected_size')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('online_order_items') && Schema::hasColumn('online_order_items', 'selected_size')) {
            Schema::table('online_order_items', function (Blueprint $table) {
                $table->dropColumn('selected_size');
            });
        }
    }
};
