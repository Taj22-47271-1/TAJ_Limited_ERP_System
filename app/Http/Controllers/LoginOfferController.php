<?php

namespace App\Http\Controllers;

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

        return view('admin.login-offers', compact('settings', 'offersConfig'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'interval_seconds' => ['required', 'integer', 'min:2', 'max:20'],
            'slides' => ['nullable', 'array'],
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

        if (is_array($rawSlides)) {
            foreach ($rawSlides as $index => $slideData) {
                $title = trim($slideData['title'] ?? '');
                if ($title === '') {
                    continue; // Skip completely empty slides
                }

                // Check for uploaded file for this slide index
                $imagePath = $slideData['existing_image'] ?? 'assets/images/offers/eid-offer.svg';
                if ($request->hasFile("slide_image_{$index}")) {
                    $uploaded = $request->file("slide_image_{$index}")->store('offers', 'public');
                    $imagePath = 'storage/' . $uploaded;
                }

                $processedSlides[] = [
                    'id' => count($processedSlides) + 1,
                    'badge' => trim($slideData['badge'] ?? 'SPECIAL OFFER'),
                    'discount' => trim($slideData['discount'] ?? '10% DISCOUNT'),
                    'title' => $title,
                    'subtitle' => trim($slideData['subtitle'] ?? ''),
                    'image' => $imagePath,
                    'tag' => trim($slideData['tag'] ?? 'FESTIVE OFFER'),
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
