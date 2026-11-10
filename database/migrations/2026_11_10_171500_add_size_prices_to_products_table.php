<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'size_prices')) {
            Schema::table('products', function (Blueprint $table) {
                $table->json('size_prices')->nullable()->after('size_options');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'size_prices')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('size_prices');
            });
        }
    }
};
