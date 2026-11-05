<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request)
    {
        return view('shop.cart', $this->cartData($request));
    }

    public function summary(Request $request)
    {
        $data = $this->cartData($request);
        $totalCount = collect(session('cart', []))->sum(fn ($line) => is_array($line) ? (float)($line['quantity'] ?? 0) : (float)$line);

        return response()->json([
            'cart_count' => $totalCount,
            'subtotal' => $data['subtotal'],
            'original_subtotal' => $data['originalSubtotal'],
            'discount_total' => $data['discountTotal'],
            'delivery_fee' => $data['deliveryFee'],
            'total' => $data['total'],
            'items' => $data['items']->map(fn ($it) => [
                'key' => $it['key'],
                'product_id' => $it['product']->id,
                'name' => $it['product']->name,
                'size' => $it['size'],
                'color' => $it['color'],
                'colorway' => $it['colorway'],
                'quantity' => $it['quantity'],
                'selling_price' => (float)$it['product']->effective_price,
                'original_price' => (float)$it['product']->selling_price,
                'has_discount' => $it['product']->has_discount,
                'discount_percent' => $it['product']->discount_percent_calculated,
                'line_total' => $it['line_total'],
                'image' => $it['image'],
            ]),
        ]);
    }

    public function add(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => ['nullable', 'numeric', 'min:1'],
            'size' => ['nullable', 'string', 'max:30'],
            'color' => ['nullable', 'string', 'max:60'],
            'buy_now' => ['nullable', 'boolean'],
        ]);

        abort_unless($product->status, 404);
        $size = $this->validatedSize($product, $data['size'] ?? null);
        $color = $this->validatedColor($product, $data['color'] ?? null);
        $qty = (float)($data['quantity'] ?? 1);
        $cart = $this->normalizeCart($request->session()->get('cart', []));
        $key = $this->cartKey($product->id, $size, $color);

        $alreadyInCart = collect($cart)
            ->where('product_id', $product->id)
            ->sum(fn ($line) => (float)$line['quantity']);

        if ($alreadyInCart + $qty > $product->available_stock) {
            $msg = "Only {$product->available_stock} {$product->unit} of {$product->name} are currently available.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['cart' => $msg]);
        }

        $existing = $cart[$key] ?? [
            'product_id' => $product->id,
            'size' => $size,
            'color' => $color,
            'quantity' => 0,
        ];
        $existing['quantity'] = (float)$existing['quantity'] + $qty;
        $cart[$key] = $existing;
        $request->session()->put('cart', $cart);

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout');
        }

        $label = $product->name;
        $variants = [];
        if ($color) $variants[] = "Color: {$color}";
        if ($size) $variants[] = "Size: {$size}";
        if (! empty($variants)) {
            $label .= ' (' . implode(', ', $variants) . ')';
        }

        if ($request->wantsJson() || $request->ajax()) {
            $data = $this->cartData($request);
            $totalCount = collect($cart)->sum(fn ($line) => is_array($line) ? (float)($line['quantity'] ?? 0) : (float)$line);
            return response()->json([
                'success' => true,
                'message' => $label . ' added to cart.',
                'cart_count' => $totalCount,
                'subtotal' => $data['subtotal'],
                'total' => $data['total'],
                'items' => $data['items']->map(fn ($it) => [
                    'key' => $it['key'],
                    'product_id' => $it['product']->id,
                    'name' => $it['product']->name,
                    'size' => $it['size'],
                    'color' => $it['color'],
                    'colorway' => $it['colorway'],
                    'quantity' => $it['quantity'],
                    'selling_price' => (float)$it['product']->effective_price,
                    'original_price' => (float)$it['product']->selling_price,
                    'has_discount' => $it['product']->has_discount,
                    'discount_percent' => $it['product']->discount_percent_calculated,
                    'line_total' => $it['line_total'],
                    'image' => $it['image'],
                ]),
            ]);
        }

        return back()->with('status', $label . ' added to cart.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0'],
            'size' => ['nullable', 'string', 'max:30'],
            'color' => ['nullable', 'string', 'max:60'],
        ]);

        $size = $this->validatedSize($product, $data['size'] ?? null);
        $color = $this->validatedColor($product, $data['color'] ?? null);
        $cart = $this->normalizeCart($request->session()->get('cart', []));
        $key = $this->cartKey($product->id, $size, $color);
        $qty = (float)$data['quantity'];

        if ($qty <= 0) {
            unset($cart[$key]);
        } else {
            $otherQty = collect($cart)
                ->reject(fn ($line, $lineKey) => $lineKey === $key)
                ->where('product_id', $product->id)
                ->sum(fn ($line) => (float)$line['quantity']);

            if ($otherQty + $qty > $product->available_stock) {
                $msg = "Only {$product->available_stock} available across all variants.";
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->withErrors(['cart' => $msg]);
            }

            $cart[$key] = [
                'product_id' => $product->id,
                'size' => $size,
                'color' => $color,
                'quantity' => $qty,
            ];
        }

        $request->session()->put('cart', $cart);

        if ($request->wantsJson() || $request->ajax()) {
            $data = $this->cartData($request);
            $totalCount = collect($cart)->sum(fn ($line) => is_array($line) ? (float)($line['quantity'] ?? 0) : (float)$line);
            return response()->json([
                'success' => true,
                'message' => 'Cart updated.',
                'cart_count' => $totalCount,
                'subtotal' => $data['subtotal'],
                'total' => $data['total'],
            ]);
        }

        return back()->with('status', 'Cart updated.');
    }

    public function remove(Request $request, Product $product)
    {
        $data = $request->validate([
            'size' => ['nullable', 'string', 'max:30'],
            'color' => ['nullable', 'string', 'max:60'],
        ]);
        $size = $this->normalizedSize($data['size'] ?? null);
        $color = $this->normalizedColor($data['color'] ?? null);
        $cart = $this->normalizeCart($request->session()->get('cart', []));
        unset($cart[$this->cartKey($product->id, $size, $color)]);
        $request->session()->put('cart', $cart);

        if ($request->wantsJson() || $request->ajax()) {
            $data = $this->cartData($request);
            $totalCount = collect($cart)->sum(fn ($line) => is_array($line) ? (float)($line['quantity'] ?? 0) : (float)$line);
            return response()->json([
                'success' => true,
                'message' => 'Product removed from cart.',
                'cart_count' => $totalCount,
                'subtotal' => $data['subtotal'],
                'total' => $data['total'],
            ]);
        }

        return back()->with('status', 'Product removed from cart.');
    }

    public function checkout(Request $request)
    {
        $data = $this->cartData($request);
        if ($data['items']->isEmpty()) {
            return redirect()->route('shop.index')->withErrors(['cart' => 'Your cart is empty.']);
        }
        return view('shop.checkout', $data);
    }

    private function cartData(Request $request): array
    {
        $cart = $this->normalizeCart($request->session()->get('cart', []));
        $request->session()->put('cart', $cart);

        $productIds = collect($cart)->pluck('product_id')->unique()->values()->all();
        $products = Product::with('category')->whereIn('id', $productIds)->get()->keyBy('id');

        $items = collect($cart)->map(function ($line, $key) use ($products) {
            $product = $products->get((int)$line['product_id']);
            if (! $product) {
                return null;
            }

            $size = $product->has_sizes ? ($line['size'] ?? null) : null;
            if ($product->has_sizes && ! $product->hasSize($size)) {
                return null;
            }

            $color = $line['color'] ?? null;
            $colorway = $product->findColorway($color);
            $image = (! empty($colorway['image_url'])) ? $colorway['image_url'] : $product->image_url;

            $quantity = (float)$line['quantity'];
            $effectivePrice = (float)$product->effective_price;

            return [
                'key' => $key,
                'product' => $product,
                'size' => $size,
                'color' => $colorway['name'] ?? $color,
                'colorway' => $colorway,
                'image' => $image,
                'quantity' => $quantity,
                'unit_price' => $effectivePrice,
                'original_price' => (float)$product->selling_price,
                'has_discount' => $product->has_discount,
                'discount_percent' => $product->discount_percent_calculated,
                'line_total' => $quantity * $effectivePrice,
            ];
        })->filter()->values();

        $subtotal = (float)$items->sum('line_total');
        $originalSubtotal = (float)$items->sum(fn ($it) => $it['quantity'] * $it['original_price']);
        $discountTotal = max(0, $originalSubtotal - $subtotal);
        $deliveryFee = $subtotal >= 3000 ? 0.00 : ($subtotal > 0 ? 80.00 : 0.00);
        $total = $subtotal + $deliveryFee;

        return compact('items', 'subtotal', 'originalSubtotal', 'discountTotal', 'deliveryFee', 'total');
    }

    private function normalizedSize(?string $size): ?string
    {
        $size = trim((string)$size);
        return $size === '' ? null : $size;
    }

    private function validatedSize(Product $product, ?string $size): ?string
    {
        $size = $this->normalizedSize($size);

        if (! $product->has_sizes) {
            return null;
        }

        if (! $product->hasSize($size)) {
            // Default to first size if not provided
            $availableSizes = $product->normalizedSizes();
            if (! empty($availableSizes)) {
                return $availableSizes[0];
            }
            throw ValidationException::withMessages([
                'size' => 'Please select a valid size for ' . $product->name . '.',
            ]);
        }

        return collect($product->normalizedSizes())->first(
            fn ($option) => mb_strtolower($option) === mb_strtolower((string)$size)
        );
    }

    private function normalizedColor(?string $color): ?string
    {
        $color = trim((string)$color);
        return $color === '' ? null : $color;
    }

    private function validatedColor(Product $product, ?string $color): ?string
    {
        $color = $this->normalizedColor($color);
        $colorways = $product->available_colorways;

        if (empty($colorways)) {
            return null;
        }

        if (! empty($color)) {
            $match = $product->findColorway($color);
            if ($match) {
                return $match['name'];
            }
        }

        // Default to first colorway
        return $colorways[0]['name'] ?? null;
    }

    private function cartKey(int $productId, ?string $size, ?string $color = null): string
    {
        return $productId . ':' . mb_strtolower($size ?: '_') . ':' . mb_strtolower($color ?: '_');
    }

    private function normalizeCart(array $cart): array
    {
        $normalized = [];

        foreach ($cart as $key => $value) {
            if (is_array($value)) {
                $productId = (int)($value['product_id'] ?? 0);
                $quantity = (float)($value['quantity'] ?? 0);
                $size = $this->normalizedSize($value['size'] ?? null);
                $color = $this->normalizedColor($value['color'] ?? null);
            } else {
                $productId = (int)$key;
                $quantity = (float)$value;
                $size = null;
                $color = null;
            }

            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            $lineKey = $this->cartKey($productId, $size, $color);
            if (isset($normalized[$lineKey])) {
                $normalized[$lineKey]['quantity'] += $quantity;
            } else {
                $normalized[$lineKey] = [
                    'product_id' => $productId,
                    'size' => $size,
                    'color' => $color,
                    'quantity' => $quantity,
                ];
            }
        }

        return $normalized;
    }
}
