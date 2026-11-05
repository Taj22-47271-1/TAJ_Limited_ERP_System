<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->where('status', true)->whereRaw('(stock - reserved_stock) > 0');
        if ($request->filled('q')) {
            $term = trim($request->q);
            $query->where(fn($q) => $q->where('name','like',"%{$term}%")->orWhere('sku','like',"%{$term}%"));
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->integer('category'));
        }
        return view('shop.index', [
            'products'=>$query->latest()->paginate(12)->withQueryString(),
            'categories'=>Category::where('status',true)->orderBy('name')->get(),
        ]);
    }

    public function show(Product $product)
    {
        abort_unless($product->status, 404);
        $relatedProducts = Product::where('status', true)
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn($q) => $q->where('category_id', $product->category_id))
            ->latest()
            ->limit(4)
            ->get();

        if ($relatedProducts->isEmpty()) {
            $relatedProducts = Product::where('status', true)->where('id', '!=', $product->id)->latest()->limit(4)->get();
        }

        $relatedClothing = $relatedProducts;

        return view('shop.show', compact('product', 'relatedProducts', 'relatedClothing'));
    }
}
