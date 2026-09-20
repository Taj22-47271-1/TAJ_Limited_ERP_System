<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        $addHasSizes = !Schema::hasColumn('products', 'has_sizes');
        $addSizeOptions = !Schema::hasColumn('products', 'size_options');

        if ($addHasSizes || $addSizeOptions) {
            Schema::table('products', function (Blueprint $table) use ($addHasSizes, $addSizeOptions) {
                if ($addHasSizes) {
                    $table->boolean('has_sizes')->default(false);
                }

                if ($addSizeOptions) {
                    $table->json('size_options')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        $columns = [];

        if (Schema::hasColumn('products', 'has_sizes')) {
            $columns[] = 'has_sizes';
        }

        if (Schema::hasColumn('products', 'size_options')) {
            $columns[] = 'size_options';
        }

        if (!empty($columns)) {
            Schema::table('products', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
