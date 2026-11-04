<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LoginOfferController extends Controller
{
    public function edit()
    {
        $settings = Setting::firstOrCreate(['id' => 1], [
            'company_name' => 'TAJ Limited',
            'currency_symbol' => '৳',
        ]);

        $offersConfig = $settings->getLoginOffers();
        $products = Product::where('status', true)->orderBy('name')->get();

        return view('admin.login-offers', compact('settings', 'offersConfig', 'products'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'interval_seconds' => ['required', 'integer', 'min:2', 'max:20'],
            'slides' => ['nullable', 'array'],
            'slides.*.product_id' => ['nullable', 'exists:products,id'],
            'slides.*.badge' => ['nullable', 'string', 'max:100'],
            'slides.*.discount' => ['nullable', 'string', 'max:60'],
            'slides.*.title' => ['nullable', 'string', 'max:150'],
            'slides.*.subtitle' => ['nullable', 'string', 'max:350'],
            'slides.*.tag' => ['nullable', 'string', 'max:60'],
            'slides.*.existing_image' => ['nullable', 'string'],
        ]);

        $settings = Setting::firstOrCreate(['id' => 1]);
        $currentConfig = $settings->getLoginOffers();

        $processedSlides = [];
        $rawSlides = $request->input('slides', []);
        $allProducts = Product::where('status', true)->get()->keyBy('id');

        if (is_array($rawSlides)) {
            foreach ($rawSlides as $index => $slideData) {
                $title = trim($slideData['title'] ?? '');
                $productId = !empty($slideData['product_id']) ? (int)$slideData['product_id'] : null;
                $linkedProduct = $productId ? $allProducts->get($productId) : null;

                if ($title === '' && ! $linkedProduct) {
                    continue; // Skip completely empty slides
                }

                if ($title === '' && $linkedProduct) {
                    $title = $linkedProduct->name;
                }

                // Check for uploaded file for this slide index
                $imagePath = $slideData['existing_image'] ?? '';
                if ($request->hasFile("slide_image_{$index}")) {
                    $uploaded = $request->file("slide_image_{$index}")->store('offers', 'public');
                    $imagePath = 'storage/' . $uploaded;
                } elseif (empty($imagePath) && $linkedProduct) {
                    $imagePath = $linkedProduct->image_path;
                } elseif (empty($imagePath)) {
                    $imagePath = 'assets/images/offers/eid-offer.svg';
                }

                $origPrice = $linkedProduct ? (float)$linkedProduct->selling_price : 0;
                $finalPrice = $linkedProduct ? (float)$linkedProduct->effective_price : 0;
                $discountPercent = $linkedProduct ? $linkedProduct->discount_percent_calculated : null;

                $processedSlides[] = [
                    'id' => count($processedSlides) + 1,
                    'product_id' => $productId,
                    'product_sku' => $linkedProduct?->sku,
                    'product_name' => $linkedProduct?->name ?? '',
                    'original_price' => $origPrice,
                    'discount_price' => $finalPrice,
                    'discount_percent' => $discountPercent,
                    'has_discount' => $linkedProduct?->has_discount ?? false,
                    'badge' => trim($slideData['badge'] ?? 'SPECIAL OFFER'),
                    'discount' => trim($slideData['discount'] ?? ($discountPercent ? "{$discountPercent}% OFF" : 'FESTIVE OFFER')),
                    'title' => $title,
                    'subtitle' => trim($slideData['subtitle'] ?? ($linkedProduct?->description ?? '')),
                    'image' => $imagePath,
                    'tag' => trim($slideData['tag'] ?? 'OFFER'),
                ];
            }
        }

        // If no valid slides left, keep default or previous
        if (empty($processedSlides)) {
            $processedSlides = $currentConfig['slides'] ?? Setting::defaultLoginOffers()['slides'];
        }

        $newConfig = [
            'enabled' => $request->boolean('enabled'),
            'interval_seconds' => (int) $data['interval_seconds'],
            'slides' => $processedSlides,
        ];

        $settings->update(['login_offers' => $newConfig]);
        Cache::forget('company.settings');

        return back()->with('status', 'Login promotional offer banners & slider configuration updated successfully!');
    }
}
