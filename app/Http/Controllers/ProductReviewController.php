<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        // 1. Self-healing: Ensure product_reviews table exists on any environment
        try {
            if (! Schema::hasTable('product_reviews')) {
                Artisan::call('migrate', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            Log::warning('Auto-migration for product_reviews check: ' . $e->getMessage());
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'review' => ['required', 'string', 'min:3', 'max:1500'],
        ]);

        $name = trim($validated['customer_name'] ?? '');
        if ($name === '') {
            $name = auth()->check() ? auth()->user()->name : 'Satisfied Customer';
        }

        $email = $validated['customer_email'] ?? (auth()->check() ? auth()->user()->email : null);

        // Verify valid user_id to prevent foreign key constraint violations
        $userId = auth()->id();
        if ($userId && ! User::where('id', $userId)->exists()) {
            $userId = null;
        }

        try {
            ProductReview::create([
                'product_id' => $product->id,
                'user_id' => $userId,
                'customer_name' => $name,
                'customer_email' => $email,
                'rating' => (int) $validated['rating'],
                'review' => strip_tags($validated['review']),
                'is_verified' => true,
                'status' => 'approved',
            ]);

            $message = 'Thank you! Your rating and review have been published.';

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }

            return redirect()->route('shop.show', $product)->with('status', $message);
        } catch (\Throwable $e) {
            Log::error('Failed to save review: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to submit review right now. Please try again.',
                ], 500);
            }

            return redirect()->route('shop.show', $product)->withErrors([
                'review' => 'Unable to submit review: ' . $e->getMessage(),
            ]);
        }
    }
}
