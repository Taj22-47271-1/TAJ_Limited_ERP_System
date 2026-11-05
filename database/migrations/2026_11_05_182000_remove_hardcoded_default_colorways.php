<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Removes pre-seeded default colorway swatches so that colorways are strictly user-managed.
     */
    public function up(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'colorways')) {
            // Clear default hardcoded colorways on existing seed data
            DB::table('products')->update(['colorways' => null]);
        }

        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'showcase_config')) {
            $settings = DB::table('settings')->get();
            foreach ($settings as $setting) {
                if ($setting->showcase_config) {
                    $decoded = json_decode($setting->showcase_config, true);
                    if (is_array($decoded) && isset($decoded['swatches'])) {
                        $decoded['swatches'] = [];
                        DB::table('settings')
                            ->where('id', $setting->id)
                            ->update(['showcase_config' => json_encode($decoded)]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed as user can add colorways via Admin panel.
    }
};
