<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\ImageBackgroundService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

        $data['colorways'] = $this->processColorways($request, $imageService, $data['sku'] ?? 'PROD', []);

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

        $data['colorways'] = $this->processColorways($request, $imageService, $data['sku'] ?? $product->sku, $product->colorways ?? []);

        $product->update($data);
        return redirect()->route('products.index')->with('status','Product updated successfully.');
    }

    public function destroy(Request $request, Product $product)
    {
        abort_unless($request->user()->hasPermission('inventory.manage'), 403);

        $productName = $product->name;

        // Clean up any uploaded colorway images from public storage
        if (! empty($product->colorways) && is_array($product->colorways)) {
            foreach ($product->colorways as $cw) {
                $cwImg = $cw['image'] ?? null;
                if ($cwImg && ! str_starts_with($cwImg, 'assets/') && ! str_starts_with($cwImg, 'http')) {
                    Storage::disk('public')->delete($cwImg);
                    \App\Models\StoredFile::where('file_path', $cwImg)->delete();
                }
            }
        }
        if ($product->image_path && ! str_starts_with($product->image_path, 'assets/') && ! str_starts_with($product->image_path, 'http')) {
            \App\Models\StoredFile::where('file_path', $product->image_path)->delete();
        }

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
            'discount_price'=>['nullable','numeric','min:0'],
            'discount_percent'=>['nullable','numeric','min:0','max:100'],
            'min_stock'=>['required','numeric','min:0'],
            'description'=>['nullable','string','max:3000'],
            'status'=>['nullable','boolean'],
            'image'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'has_sizes'=>['nullable','boolean'],
            'sizes_text'=>['nullable','string','max:300'],
            'size_prices'=>['nullable','array'],
            'size_prices.*.selling_price'=>['nullable','numeric','min:0'],
            'size_prices.*.discount_price'=>['nullable','numeric','min:0'],
            'size_prices.*.discount_percent'=>['nullable','numeric','min:0','max:100'],
            'colorways'=>['nullable','array'],
            'colorways.*.name'=>['nullable','string','max:100'],
            'colorways.*.color'=>['nullable','string','max:30'],
            'colorways.*.existing_image'=>['nullable','string','max:500'],
            'colorways.*.image_file'=>['nullable','file','image','mimes:jpg,jpeg,png,webp','max:4096'],
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

        // Process size-specific pricing if configured
        $rawSizePrices = $request->input('size_prices');
        $cleanSizePrices = [];
        if ($hasSizes && is_array($rawSizePrices)) {
            foreach ($rawSizePrices as $sizeKey => $szData) {
                if (! is_array($szData)) continue;
                $trimmedKey = trim((string)$sizeKey);
                if (! $sizes->contains(fn ($s) => mb_strtolower($s) === mb_strtolower($trimmedKey))) {
                    continue;
                }

                $szSelling = isset($szData['selling_price']) && $szData['selling_price'] !== '' ? (float)$szData['selling_price'] : null;
                $szDiscount = isset($szData['discount_price']) && $szData['discount_price'] !== '' ? (float)$szData['discount_price'] : null;
                $szPercent = isset($szData['discount_percent']) && $szData['discount_percent'] !== '' ? (float)$szData['discount_percent'] : null;

                if ($szSelling !== null && $szSelling > 0) {
                    if ($szDiscount !== null && $szDiscount > 0 && $szDiscount < $szSelling) {
                        $szPercent = round((($szSelling - $szDiscount) / $szSelling) * 100, 1);
                    } elseif ($szPercent !== null && $szPercent > 0 && $szPercent < 100) {
                        $szDiscount = round($szSelling * (1 - ($szPercent / 100)), 2);
                    } else {
                        $szDiscount = null;
                        $szPercent = null;
                    }

                    $cleanSizePrices[$trimmedKey] = [
                        'selling_price' => round($szSelling, 2),
                        'discount_price' => $szDiscount ? round($szDiscount, 2) : null,
                        'discount_percent' => $szPercent ? round($szPercent, 1) : null,
                    ];
                }
            }
        }
        $data['size_prices'] = ! empty($cleanSizePrices) ? $cleanSizePrices : null;

        // Auto-calculate and synchronize discount price & discount percentage
        $sellingPrice = (float)($data['selling_price'] ?? 0);
        $discountPrice = isset($data['discount_price']) && $data['discount_price'] !== '' ? (float)$data['discount_price'] : null;
        $discountPercent = isset($data['discount_percent']) && $data['discount_percent'] !== '' ? (float)$data['discount_percent'] : null;

        if ($discountPrice !== null && $discountPrice > 0 && $discountPrice < $sellingPrice && $sellingPrice > 0) {
            $data['discount_price'] = round($discountPrice, 2);
            $data['discount_percent'] = round((($sellingPrice - $discountPrice) / $sellingPrice) * 100, 1);
        } elseif ($discountPercent !== null && $discountPercent > 0 && $discountPercent < 100 && $sellingPrice > 0) {
            $data['discount_percent'] = round($discountPercent, 1);
            $data['discount_price'] = round($sellingPrice * (1 - ($discountPercent / 100)), 2);
        } else {
            $data['discount_price'] = null;
            $data['discount_percent'] = null;
        }

        return $data;
    }

    private function processColorways(Request $request, ImageBackgroundService $imageService, string $productSku, ?array $previousColorways = []): ?array
    {
        $rawColorways = $request->input('colorways');
        if (! is_array($rawColorways) || empty($rawColorways)) {
            // If previous colorways had uploaded files that are now completely removed, clean them up
            if (! empty($previousColorways) && is_array($previousColorways)) {
                foreach ($previousColorways as $oldCw) {
                    $oldImg = $oldCw['image'] ?? null;
                    if ($oldImg && ! str_starts_with($oldImg, 'assets/') && ! str_starts_with($oldImg, 'http')) {
                        Storage::disk('public')->delete($oldImg);
                    }
                }
            }
            return null;
        }

        $processed = [];
        $retainedImages = [];

        foreach ($rawColorways as $idx => $cw) {
            $name = trim($cw['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $color = trim($cw['color'] ?? '#1e293b');
            $existingImg = trim($cw['existing_image'] ?? '');
            $imgStored = null;

            // Check if a new image was uploaded for this row
            $file = null;
            if ($request->hasFile("colorways.{$idx}.image_file")) {
                $file = $request->file("colorways.{$idx}.image_file");
            } elseif ($request->file('colorways') && isset($request->file('colorways')[$idx]['image_file'])) {
                $file = $request->file('colorways')[$idx]['image_file'];
            }

            if ($file && $file->isValid()) {
                $imgStored = $imageService->processAndStore($file, 'public', 'products/colorways');
                if ($existingImg && ! str_starts_with($existingImg, 'assets/') && ! str_starts_with($existingImg, 'http')) {
                    Storage::disk('public')->delete($existingImg);
                }
            } elseif (! empty($existingImg)) {
                $imgStored = $existingImg;
            }

            if ($imgStored) {
                $retainedImages[] = $imgStored;
            }

            $slugKey = Str::slug($name, '_') ?: ('color_' . count($processed));
            $skuSuffix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 4) ?: 'CW' . count($processed));

            $processed[] = [
                'key' => $slugKey,
                'name' => $name,
                'color' => $color,
                'sku' => $productSku . '-' . $skuSuffix,
                'image' => $imgStored,
            ];
        }

        // Cleanup any old images that were removed in this edit
        if (! empty($previousColorways) && is_array($previousColorways)) {
            foreach ($previousColorways as $oldCw) {
                $oldImg = $oldCw['image'] ?? null;
                if ($oldImg && ! in_array($oldImg, $retainedImages, true)) {
                    if (! str_starts_with($oldImg, 'assets/') && ! str_starts_with($oldImg, 'http')) {
                        Storage::disk('public')->delete($oldImg);
                    }
                }
            }
        }

        return ! empty($processed) ? $processed : null;
    }
}
