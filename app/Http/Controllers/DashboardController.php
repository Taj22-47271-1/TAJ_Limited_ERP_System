<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\OnlineOrder;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $today = now()->toDateString();

        if ($user->role === 'customer') {
            $counts = OnlineOrder::where('user_id', $user->id)
                ->selectRaw("
                    COUNT(*) as total,
                    COUNT(CASE WHEN order_status = 'pending' THEN 1 END) as pending,
                    COUNT(CASE WHEN order_status IN ('approved','processing','packed','shipped','out_for_delivery') THEN 1 END) as processing,
                    COUNT(CASE WHEN order_status = 'delivered' THEN 1 END) as delivered
                ")->first();
            $orderCount = (int) ($counts->total ?? 0);
            $pendingCount = (int) ($counts->pending ?? 0);
            $processingCount = (int) ($counts->processing ?? 0);
            $deliveredCount = (int) ($counts->delivered ?? 0);
            $recentOrders = OnlineOrder::where('user_id', $user->id)->latest()->limit(6)->get();

            $featuredProducts = Cache::remember('storefront_featured_products', 30, function () {
                return Product::where('status', true)->whereRaw('(stock - reserved_stock) > 0')->with('category')->latest()->limit(24)->get();
            });

            $perfumeProducts = Cache::remember('storefront_perfume_products', 30, function () {
                return Product::where('status', true)
                    ->whereRaw('(stock - reserved_stock) > 0')
                    ->where(function ($q) {
                        $q->whereHas('category', fn ($c) => $c->where('name', 'like', '%perfume%')->orWhere('name', 'like', '%fragrance%'))
                          ->orWhere('sku', 'like', 'PERF-%');
                    })
                    ->with('category')
                    ->latest()
                    ->get();
            });

            $clothingProducts = Cache::remember('storefront_clothing_products', 30, function () {
                return Product::where('status', true)
                    ->whereRaw('(stock - reserved_stock) > 0')
                    ->where(function ($q) {
                        $q->where('has_sizes', true)
                          ->orWhereHas('category', fn ($c) => $c->where('name', 'like', '%cloth%')->orWhere('name', 'like', '%saree%'));
                    })
                    ->with('category')
                    ->latest()
                    ->get();
            });

            return view('dashboards.customer', compact('orderCount','pendingCount','processingCount','deliveredCount','recentOrders','featuredProducts','perfumeProducts','clothingProducts'));
        }

        if ($user->role !== 'admin' && $user->role !== 'customer') {
            $mySalesStats = Sale::where('created_by', $user->id)
                ->whereNull('online_order_id')
                ->whereDate('sale_date', $today)
                ->selectRaw("COALESCE(SUM(total), 0) as total, COUNT(*) as invoices")
                ->first();
            $mySalesToday = (float) ($mySalesStats->total ?? 0);
            $myInvoicesToday = (int) ($mySalesStats->invoices ?? 0);
            $myRecentSales = Sale::with('customer')->where('created_by', $user->id)->whereNull('online_order_id')->latest('id')->limit(8)->get();
            $products = Product::where('status', true)->orderBy('name')->limit(10)->get();
            $customerCount = Customer::where('status', true)->count();

            $orderCounts = OnlineOrder::selectRaw("
                COUNT(CASE WHEN order_status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN order_status IN ('approved','processing','packed','shipped','out_for_delivery') THEN 1 END) as active
            ")->first();
            $pendingOrders = (int) ($orderCounts->pending ?? 0);
            $activeOrders = (int) ($orderCounts->active ?? 0);

            return view('dashboards.cashier', compact('mySalesToday','myInvoicesToday','myRecentSales','products','customerCount','pendingOrders','activeOrders'));
        }

        // Cache admin aggregated analytics for 20 seconds to eliminate database round-trip lag
        $stats = Cache::remember('admin_dashboard_metrics', 20, function () use ($today) {
            $salesAgg = Sale::whereDate('sale_date', $today)
                ->selectRaw("
                    COALESCE(SUM(total), 0) as total,
                    COALESCE(SUM(CASE WHEN online_order_id IS NULL THEN total ELSE 0 END), 0) as inhouse,
                    COALESCE(SUM(CASE WHEN online_order_id IS NOT NULL THEN total ELSE 0 END), 0) as online
                ")->first();
            $salesToday = (float) ($salesAgg->total ?? 0);
            $inhouseSalesToday = (float) ($salesAgg->inhouse ?? 0);
            $onlineSalesToday = (float) ($salesAgg->online ?? 0);

            $purchasesToday = (float) Purchase::whereDate('purchase_date', $today)->sum('total');
            $expensesToday = (float) Expense::whereDate('expense_date', $today)->sum('amount');
            $grossToday = (float) SaleItem::whereHas('sale', fn ($q) => $q->whereDate('sale_date', $today))
                ->selectRaw('COALESCE(SUM((unit_price-unit_cost)*quantity),0) as p')->value('p');
            $profitToday = $grossToday - $expensesToday;
            $grossAll = (float) SaleItem::selectRaw('COALESCE(SUM((unit_price-unit_cost)*quantity),0) as p')->value('p');
            $totalExpenses = (float) Expense::sum('amount');
            $totalProfit = $grossAll - $totalExpenses;
            $stockValue = (float) Product::selectRaw('COALESCE(SUM(stock * purchase_price),0) as v')->value('v');
            $productCount = Product::count();

            $userAgg = User::selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN role = 'customer' THEN 1 END) as customers,
                COUNT(CASE WHEN role = 'cashier' THEN 1 END) as cashiers,
                COUNT(CASE WHEN is_active = true THEN 1 END) as active
            ")->first();
            $customerCount = (int) ($userAgg->customers ?? 0);
            $cashierCount = (int) ($userAgg->cashiers ?? 0);
            $userCount = (int) ($userAgg->total ?? 0);
            $activeUserCount = (int) ($userAgg->active ?? 0);

            $raw = Sale::selectRaw('sale_date as day, SUM(total) as total')
                ->whereBetween('sale_date', [now()->subDays(6)->toDateString(), $today])
                ->groupBy('sale_date')->pluck('total','day');
            $salesChart = collect(range(6,0))->map(function ($i) use ($raw) {
                $d = now()->subDays($i);
                return ['label'=>$d->format('D'),'date'=>$d->toDateString(),'total'=>(float)($raw[$d->toDateString()] ?? 0)];
            });
            $max = max(1, (float) $salesChart->max('total'));
            $salesChart = $salesChart->map(fn ($row) => $row + ['height'=>max(8, round(($row['total'] / $max) * 100))]);

            return compact(
                'salesToday','inhouseSalesToday','onlineSalesToday','purchasesToday','expensesToday',
                'profitToday','totalProfit','stockValue','salesChart','productCount','customerCount',
                'cashierCount','userCount','activeUserCount'
            );
        });

        $lowStock = Product::whereColumn('stock','<=','min_stock')->orderBy('stock')->limit(6)->get();
        $recentSales = Sale::with('customer')->latest('id')->limit(7)->get();
        $recentOrders = OnlineOrder::latest()->limit(6)->get();
        $pendingOrders = OnlineOrder::where('order_status','pending')->count();
        $categories = Category::where('status', true)->orderBy('name')->get();

        return view('dashboards.admin', array_merge($stats, compact('lowStock','recentSales','recentOrders','pendingOrders','categories')));
    }
}
