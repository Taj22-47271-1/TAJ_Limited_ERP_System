<?php
namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with('product')->latest();
        if ($request->filled('type')) {
            $query->where('movement_type', $request->type);
        }
        if ($request->filled('q')) {
            $term = $request->q;
            $query->whereHas('product', fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%"));
        }

        return view('inventory.stock-movements', ['movements' => $query->paginate(25)->withQueryString()]);
    }
}
