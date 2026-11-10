<?php

namespace App\Providers;

use App\Models\OnlineOrder;
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
        // Force HTTPS URLs in production (Render) or when forwarded as HTTPS
        if ($this->app->environment('production') || env('FORCE_HTTPS', false) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
            URL::forceScheme('https');
        }

        // Share company settings with all Blade views (zero-latency in-memory memoization)
        View::composer('*', function ($view): void {
            static $cachedSettings = null;
            if ($cachedSettings !== null) {
                $view->with('companySettings', $cachedSettings);
                return;
            }

            $fallback = new class {
                public string $company_name = 'TAJ Limited';
                public string $company_email = 'info@tajlimited.com';
                public string $company_phone = '+880 1XXXXXXXXX';
                public string $company_address = 'Dhaka, Bangladesh';
                public string $currency_symbol = '৳';
                public function getShowcaseSettings(): array { return \App\Models\Setting::defaultShowcaseConfig(); }
                public function getLoginOffers(): array { return \App\Models\Setting::defaultLoginOffers(); }
            };

            try {
                $cachedSettings = Cache::remember(
                    'company.settings',
                    300,
                    fn () => Setting::query()->first() ?? $fallback
                );
            } catch (Throwable) {
                $cachedSettings = $fallback;
            }

            $view->with('companySettings', $cachedSettings);
        });

        // Share pending online orders count with application layouts and dashboards
        View::composer(['layouts.app', 'layouts.admin', 'layouts.cashier', 'dashboards.*'], function ($view): void {
            static $cachedPendingCount = null;
            if ($cachedPendingCount !== null) {
                $view->with('pendingOrdersCount', $cachedPendingCount);
                return;
            }

            try {
                $cachedPendingCount = Cache::remember(
                    'pending_orders_count',
                    30,
                    fn () => OnlineOrder::where('order_status', 'pending')->count()
                );
            } catch (Throwable) {
                $cachedPendingCount = 0;
            }

            $view->with('pendingOrdersCount', $cachedPendingCount);
        });
    }
}
