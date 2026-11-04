<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Add login_offers JSON column to settings
        if (Schema::hasTable('settings') && ! Schema::hasColumn('settings', 'login_offers')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->json('login_offers')->nullable()->after('showcase_config');
            });
        }

        // 2. Safe performance indexes
        try {
            if (Schema::hasTable('sales')) {
                Schema::table('sales', function (Blueprint $table) {
                    $table->index('sale_date');
                });
            }
        } catch (\Throwable $e) {
        }

        try {
            if (Schema::hasTable('expenses')) {
                Schema::table('expenses', function (Blueprint $table) {
                    $table->index('expense_date');
                });
            }
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'login_offers')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('login_offers');
            });
        }
    }
};
