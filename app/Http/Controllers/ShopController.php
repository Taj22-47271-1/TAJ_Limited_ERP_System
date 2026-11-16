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
            $lowerTerm = '%' . strtolower($term) . '%';
            $query->where(function ($q) use ($lowerTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', [$lowerTerm])
                  ->orWhereRaw('LOWER(sku) LIKE ?', [$lowerTerm])
                  ->orWhereRaw('LOWER(COALESCE(description, \'\')) LIKE ?', [$lowerTerm]);
            });
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->integer('category'));
        }
        if ($request->get('filter') === 'sale') {
            $query->where('discount_price', '>', 0);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'products' => $query->latest()->limit(8)->get()->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'category' => $p->category?->name,
                    'effective_price' => (float)$p->effective_price,
                    'selling_price' => (float)$p->selling_price,
                    'has_discount' => (bool)$p->has_discount,
                    'discount_percent' => $p->discount_percent_calculated,
                    'image' => $p->image_url,
                    'url' => route('shop.show', $p),
                ]),
            ]);
        }

        return view('shop.index', [
            'products' => $query->latest()->paginate(12)->withQueryString(),
            'categories' => Category::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function show(Product $product)
    {
        abort_unless($product->status, 404);
        $product->load(['reviews' => fn($q) => $q->latest()]);
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
