<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShowcaseController extends Controller
{
    public function edit(Request $request)
    {
        $settings = Setting::firstOrCreate(['id' => 1], [
            'company_name' => 'TAJ Limited',
            'currency_symbol' => '৳',
        ]);

        $clothingProducts = Product::where('status', true)
            ->where(function ($q) {
                $q->where('has_sizes', true)
                  ->orWhereHas('category', fn ($c) => $c->where('name', 'like', '%cloth%')->orWhere('name', 'like', '%saree%'));
            })
            ->orderBy('name')
            ->get();

        $selectedProduct = null;
        if ($request->filled('product_id')) {
            $selectedProduct = Product::find($request->product_id);
        }

        $config = $selectedProduct
            ? $selectedProduct->getEffectiveShowcaseConfig($settings)
            : $settings->getShowcaseSettings();

        return view('admin.showcase', compact('settings', 'clothingProducts', 'selectedProduct', 'config'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['nullable', 'exists:products,id'],
            'enabled' => ['nullable', 'boolean'],
            'enable_3d' => ['nullable', 'boolean'],
            'tagline' => ['required', 'string', 'max:255'],
            'feature_1' => ['nullable', 'string', 'max:120'],
            'feature_2' => ['nullable', 'string', 'max:120'],
            'feature_3' => ['nullable', 'string', 'max:120'],
            'loupe_kicker' => ['required', 'string', 'max:100'],
            'loupe_title' => ['required', 'string', 'max:200'],
            'loupe_desc' => ['nullable', 'string', 'max:600'],
            'loupe_image' => ['nullable', 'image', 'max:4096'],
            'specs' => ['nullable', 'array'],
            'specs.*.label' => ['nullable', 'string', 'max:60'],
            'specs.*.value' => ['nullable', 'string', 'max:120'],
            'hotspots' => ['nullable', 'array'],
            'hotspots.*.title' => ['nullable', 'string', 'max:80'],
            'hotspots.*.desc' => ['nullable', 'string', 'max:300'],
            'hotspots.*.top' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'hotspots.*.left' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'swatches' => ['nullable', 'array'],
            'swatches.*.name' => ['nullable', 'string', 'max:50'],
            'swatches.*.color' => ['nullable', 'string', 'max:30'],
            'swatches.*.tag' => ['nullable', 'string', 'max:50'],
            'swatches.*.existing_image' => ['nullable', 'string'],
            'gallery_images.*' => ['nullable', 'image', 'max:4096'],
        ]);

        $settings = Setting::firstOrCreate(['id' => 1]);
        $currentConfig = $settings->getShowcaseSettings();

        // Handle custom loupe image upload
        $loupeImagePath = $currentConfig['loupe']['image'] ?? 'assets/images/products/saree-fabric-texture.jpg';
        if ($request->hasFile('loupe_image')) {
            $path = $request->file('loupe_image')->store('showcase', 'public');
            $loupeImagePath = 'storage/' . $path;
        }

        // Format Specs
        $specs = [];
        if (! empty($data['specs'])) {
            foreach ($data['specs'] as $spec) {
                if (! empty($spec['label']) && ! empty($spec['value'])) {
                    $specs[] = [
                        'label' => trim($spec['label']),
                        'value' => trim($spec['value']),
                    ];
                }
            }
        }
        if (empty($specs)) {
            $specs = $currentConfig['loupe']['specs'];
        }

        // Format Hotspots
        $hotspots = [];
        if (! empty($data['hotspots'])) {
            $defaults = Setting::defaultShowcaseConfig()['hotspots'];
            foreach ($data['hotspots'] as $i => $spot) {
                $hotspots[] = [
                    'id' => $i + 1,
                    'top' => (float)($spot['top'] ?? ($defaults[$i]['top'] ?? 50)),
                    'left' => (float)($spot['left'] ?? ($defaults[$i]['left'] ?? 50)),
                    'title' => trim($spot['title'] ?? ($defaults[$i]['title'] ?? "Detail #".($i+1))),
                    'desc' => trim($spot['desc'] ?? ($defaults[$i]['desc'] ?? "")),
                ];
            }
        }
        if (empty($hotspots)) {
            $hotspots = $currentConfig['hotspots'];
        }

        // Format Swatches (with uploaded variant product photos)
        $swatches = [];
        if (! empty($data['swatches'])) {
            $defaultSwatches = Setting::defaultShowcaseConfig()['swatches'];
            foreach ($data['swatches'] as $i => $sw) {
                $swatchImg = $sw['existing_image'] ?? ($currentConfig['swatches'][$i]['image'] ?? '');
                if ($request->hasFile("swatch_image_{$i}")) {
                    $savedPath = $request->file("swatch_image_{$i}")->store('showcase/swatches', 'public');
                    $swatchImg = 'storage/' . $savedPath;
                }

                $swatches[] = [
                    'key' => $defaultSwatches[$i]['key'] ?? 'color_'.$i,
                    'name' => trim($sw['name'] ?? ($defaultSwatches[$i]['name'] ?? 'Color')),
                    'color' => trim($sw['color'] ?? ($defaultSwatches[$i]['color'] ?? '#d4af37')),
                    'tag' => trim($sw['tag'] ?? ($defaultSwatches[$i]['tag'] ?? '')),
                    'image' => $swatchImg,
                ];
            }
        }
        if (empty($swatches)) {
            $swatches = $currentConfig['swatches'];
        }

        // Handle multiple lookbook / gallery photos
        $gallery = $currentConfig['gallery'] ?? [];
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $path = $file->store('showcase/gallery', 'public');
                $gallery[] = 'storage/' . $path;
            }
        }

        $newConfig = [
            'enabled' => $request->boolean('enabled'),
            'enable_3d' => $request->boolean('enable_3d'),
            'tagline' => $data['tagline'],
            'features' => [
                'feature_1' => $data['feature_1'] ?? '🌿 100% Breathable Weave',
                'feature_2' => $data['feature_2'] ?? '✨ Golden Zari & Fine Stitching',
                'feature_3' => $data['feature_3'] ?? '🚚 Express Secure Delivery',
            ],
            'loupe' => [
                'kicker' => $data['loupe_kicker'],
                'title' => $data['loupe_title'],
                'desc' => $data['loupe_desc'] ?? '',
                'image' => $loupeImagePath,
                'specs' => $specs,
            ],
            'hotspots' => $hotspots,
            'swatches' => $swatches,
            'gallery' => $gallery,
        ];

        if (! empty($data['product_id'])) {
            $product = Product::findOrFail($data['product_id']);
            $product->update(['showcase_config' => $newConfig]);
            return back()->with('status', "Custom 3D showcase options for '{$product->name}' updated successfully!");
        }

        $settings->update(['showcase_config' => $newConfig]);
        Cache::forget('company.settings');

        return back()->with('status', 'Global 3D Clothing Showcase options updated successfully!');
    }
}
