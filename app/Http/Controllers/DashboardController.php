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

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $today = now()->toDateString();

        if ($user->role === 'customer') {
            $base = OnlineOrder::where('user_id', $user->id);
            $orderCount = (clone $base)->count();
            $pendingCount = (clone $base)->where('order_status','pending')->count();
            $processingCount = (clone $base)->whereIn('order_status',['approved','processing','packed','shipped','out_for_delivery'])->count();
            $deliveredCount = (clone $base)->where('order_status','delivered')->count();
            $recentOrders = (clone $base)->latest()->limit(6)->get();
            $featuredProducts = Product::where('status',true)->whereRaw('(stock - reserved_stock) > 0')->latest()->limit(8)->get();
            return view('dashboards.customer', compact('orderCount','pendingCount','processingCount','deliveredCount','recentOrders','featuredProducts'));
        }

        if ($user->role !== 'admin' && $user->role !== 'customer') {
            $mySalesToday = (float) Sale::where('created_by', $user->id)->whereNull('online_order_id')->whereDate('sale_date', $today)->sum('total');
            $myInvoicesToday = Sale::where('created_by', $user->id)->whereNull('online_order_id')->whereDate('sale_date', $today)->count();
            $myRecentSales = Sale::with('customer')->where('created_by', $user->id)->whereNull('online_order_id')->latest('id')->limit(8)->get();
            $products = Product::where('status', true)->orderBy('name')->limit(10)->get();
            $customerCount = Customer::where('status', true)->count();
            $pendingOrders = OnlineOrder::where('order_status','pending')->count();
            $activeOrders = OnlineOrder::whereIn('order_status',['approved','processing','packed','shipped','out_for_delivery'])->count();
            return view('dashboards.cashier', compact('mySalesToday','myInvoicesToday','myRecentSales','products','customerCount','pendingOrders','activeOrders'));
        }

        $salesToday = (float) Sale::whereDate('sale_date', $today)->sum('total');
        $inhouseSalesToday = (float) Sale::whereNull('online_order_id')->whereDate('sale_date', $today)->sum('total');
        $onlineSalesToday = (float) Sale::whereNotNull('online_order_id')->whereDate('sale_date', $today)->sum('total');
        $purchasesToday = (float) Purchase::whereDate('purchase_date', $today)->sum('total');
        $expensesToday = (float) Expense::whereDate('expense_date', $today)->sum('amount');
        $grossToday = (float) SaleItem::whereHas('sale', fn ($q) => $q->whereDate('sale_date', $today))
            ->selectRaw('COALESCE(SUM((unit_price-unit_cost)*quantity),0) as p')->value('p');
        $profitToday = $grossToday - $expensesToday;
        $grossAll = (float) SaleItem::selectRaw('COALESCE(SUM((unit_price-unit_cost)*quantity),0) as p')->value('p');
        $totalExpenses = (float) Expense::sum('amount');
        $totalProfit = $grossAll - $totalExpenses;
        $stockValue = (float) Product::selectRaw('COALESCE(SUM(stock * purchase_price),0) as v')->value('v');
        $lowStock = Product::whereColumn('stock','<=','min_stock')->orderBy('stock')->limit(6)->get();
        $recentSales = Sale::with('customer')->latest('id')->limit(7)->get();
        $recentOrders = OnlineOrder::latest()->limit(6)->get();
        $pendingOrders = OnlineOrder::where('order_status','pending')->count();
        $productCount = Product::count();
        $customerCount = User::where('role','customer')->count();
        $cashierCount = User::where('role','cashier')->count();
        $userCount = User::count();
        $activeUserCount = User::where('is_active', true)->count();
        $categories = Category::where('status', true)->orderBy('name')->get();

        $raw = Sale::selectRaw('sale_date as day, SUM(total) as total')
            ->whereBetween('sale_date', [now()->subDays(6)->toDateString(), $today])
            ->groupBy('sale_date')->pluck('total','day');
        $salesChart = collect(range(6,0))->map(function ($i) use ($raw) {
            $d = now()->subDays($i);
            return ['label'=>$d->format('D'),'date'=>$d->toDateString(),'total'=>(float)($raw[$d->toDateString()] ?? 0)];
        });
        $max = max(1, (float) $salesChart->max('total'));
        $salesChart = $salesChart->map(fn ($row) => $row + ['height'=>max(8, round(($row['total'] / $max) * 100))]);

        return view('dashboards.admin', compact(
            'salesToday','inhouseSalesToday','onlineSalesToday','purchasesToday','expensesToday','profitToday','totalProfit','stockValue',
            'lowStock','recentSales','recentOrders','pendingOrders','salesChart','productCount','customerCount','cashierCount','userCount','activeUserCount','categories'
        ));
    }
}
