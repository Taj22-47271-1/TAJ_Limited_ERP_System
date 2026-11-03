<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        $map = [
            'WM-001' => 'assets/images/products/wireless-mouse.svg',
            'KB-002' => 'assets/images/products/mechanical-keyboard.svg',
            'HUB-003' => 'assets/images/products/usb-c-hub.svg',
            'PAPER-004' => 'assets/images/products/a4-paper.svg',
            'WATER-005' => 'assets/images/products/mineral-water.svg',
            'COFFEE-006' => 'assets/images/products/instant-coffee.svg',
            'HDMI-007' => 'assets/images/products/hdmi-cable.svg',
            'NOTE-008' => 'assets/images/products/notebook.svg',
        ];

        foreach ($map as $sku => $image) {
            DB::table('products')
                ->where('sku', $sku)
                ->where(function ($q) {
                    $q->whereNull('image_path')->orWhere('image_path', '');
                })
                ->update(['image_path' => $image]);
        }
    }

    public function down(): void
    {
        // non-destructive
    }
};
