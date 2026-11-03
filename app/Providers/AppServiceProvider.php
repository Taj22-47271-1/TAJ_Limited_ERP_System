
<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS URLs in production (Render)
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Share company settings with all Blade views
        View::composer('*', function ($view): void {
            $fallback = (object) [
                'company_name' => config('company.name'),
                'company_email' => config('company.email'),
                'company_phone' => config('company.phone'),
                'company_address' => config('company.address'),
                'currency_symbol' => '৳',
            ];

            try {
                $settings = Schema::hasTable('settings')
                    ? Cache::remember(
                        'company.settings',
                        60,
                        fn () => Setting::query()->first() ?? $fallback
                    )
                    : $fallback;
            } catch (Throwable) {
                $settings = $fallback;
            }

            $view->with('companySettings', $settings);
        });
    }
}
