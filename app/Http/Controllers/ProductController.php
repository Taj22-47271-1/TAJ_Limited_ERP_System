<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\ImageBackgroundService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = Product::with('category')->latest();
        if ($request->filled('q')) {
            $term = trim($request->q);
            $q->where(fn($x) => $x->where('name','like',"%{$term}%")->orWhere('sku','like',"%{$term}%")->orWhere('barcode','like',"%{$term}%"));
        }
        return view('products.index', ['products'=>$q->paginate(15)->withQueryString()]);
    }

    public function create()
    {
        return view('products.form', [
            'product'=>new Product,
            'categories'=>Category::where('status',true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, InventoryService $inventory, ImageBackgroundService $imageService)
    {
        $data = $this->validated($request);
        $openingStock = $request->user()->hasPermission('inventory.manage') ? (float)($data['opening_stock'] ?? 0) : 0;
        $data = $this->prepareProductData($request, $data);
        unset($data['opening_stock'], $data['image']);
        $data['stock'] = 0;
        $data['reserved_stock'] = 0;
        $data['status'] = $request->boolean('status', true);

        if (! $request->user()->hasPermission('inventory.manage')) {
            $data['purchase_price'] = 0;
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $imageService->processAndStore($request->file('image'));
        }

        $product = DB::transaction(function () use ($data, $openingStock, $inventory, $request) {
            $product = Product::create($data);
            if ($openingStock > 0) {
                $inventory->add($product, $openingStock, (float)$product->purchase_price, 'opening_stock', null, $request->user()->id, 'Opening stock');
            }
            return $product;
        });

        return redirect()->route('products.index')->with('status', "{$product->name} created successfully.");
    }

    public function edit(Product $product)
    {
        return view('products.form', [
            'product'=>$product,
            'categories'=>Category::where('status',true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product, ImageBackgroundService $imageService)
    {
        $data = $this->validated($request, $product);
        $data = $this->prepareProductData($request, $data);
        unset($data['opening_stock'], $data['image']);
        $data['status'] = $request->boolean('status');

        if (! $request->user()->hasPermission('inventory.manage')) {
            unset($data['purchase_price']);
        }

        if ($request->hasFile('image')) {
            if ($product->image_path && !str_starts_with($product->image_path, 'assets/')) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $imageService->processAndStore($request->file('image'));
        }

        $product->update($data);
        return redirect()->route('products.index')->with('status','Product updated successfully.');
    }

    public function destroy(Request $request, Product $product)
    {
        abort_unless($request->user()->hasPermission('inventory.manage'), 403);

        $productName = $product->name;
        // Release unique SKU/barcode so they can be reused if needed
        $product->update([
            'sku' => $product->sku . '-del-' . $product->id,
            'barcode' => $product->barcode ? ($product->barcode . '-del-' . $product->id) : null,
            'status' => false,
        ]);
        $product->delete();

        return redirect()->route('products.index')->with('status', "Product '{$productName}' deleted successfully.");
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $id = $product?->id;
        $rules = [
            'category_id'=>['nullable','exists:categories,id'],
            'name'=>['required','string','max:150'],
            'sku'=>['required','max:80', Rule::unique('products','sku')->ignore($id)->whereNull('deleted_at')],
            'barcode'=>['nullable','max:100', Rule::unique('products','barcode')->ignore($id)->whereNull('deleted_at')],
            'unit'=>['required','max:30'],
            'selling_price'=>['required','numeric','min:0'],
            'min_stock'=>['required','numeric','min:0'],
            'description'=>['nullable','string','max:3000'],
            'status'=>['nullable','boolean'],
            'image'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'has_sizes'=>['nullable','boolean'],
            'sizes_text'=>['nullable','string','max:300'],
        ];

        if ($request->user()->hasPermission('inventory.manage')) {
            $rules['purchase_price'] = ['required','numeric','min:0'];
            if (! $product) {
                $rules['opening_stock'] = ['nullable','numeric','min:0'];
            }
        } else {
            $rules['purchase_price'] = ['nullable','numeric','min:0'];
        }

        return $request->validate($rules);
    }

    private function prepareProductData(Request $request, array $data): array
    {
        $hasSizes = $request->boolean('has_sizes');
        $sizes = collect(explode(',', (string)($data['sizes_text'] ?? '')))
            ->map(fn ($size) => trim($size))
            ->filter()
            ->map(fn ($size) => mb_substr($size, 0, 30))
            ->unique(fn ($size) => mb_strtolower($size))
            ->values();

        if ($hasSizes && $sizes->isEmpty()) {
            throw ValidationException::withMessages([
                'sizes_text' => 'Add at least one size, for example: S, M, L, XL.',
            ]);
        }

        unset($data['sizes_text']);
        $data['has_sizes'] = $hasSizes;
        $data['size_options'] = $hasSizes ? $sizes->all() : null;

        return $data;
    }
}
