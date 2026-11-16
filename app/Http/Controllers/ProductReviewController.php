<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'review' => ['required', 'string', 'min:4', 'max:1500'],
        ]);

        $name = trim($validated['customer_name'] ?? '');
        if ($name === '') {
            $name = auth()->check() ? auth()->user()->name : 'Satisfied Customer';
        }

        $email = $validated['customer_email'] ?? (auth()->check() ? auth()->user()->email : null);

        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
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

        return redirect()->to(url()->previous() . '#customerReviewsSection')->with('status', $message);
    }
}
