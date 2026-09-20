<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryAdjustmentController extends Controller
{
    public function store(Request $request, InventoryService $inventory)
    {
        $data = $request->validate([
            'product_id'=>['required','exists:products,id'],
            'difference'=>['required','numeric','not_in:0'],
            'reason'=>['required','string','max:250'],
        ]);

        DB::transaction(function () use ($data, $request, $inventory) {
            $product = Product::lockForUpdate()->findOrFail($data['product_id']);
            $inventory->adjust($product, (float)$data['difference'], (float)$product->purchase_price, $request->user()->id, $data['reason']);
        });

        return back()->with('status','Stock adjustment recorded.');
    }
}
