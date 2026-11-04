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
            'total' => $data['total'],
            'items' => $data['items']->map(fn ($it) => [
                'key' => $it['key'],
                'product_id' => $it['product']->id,
                'name' => $it['product']->name,
                'size' => $it['size'],
                'quantity' => $it['quantity'],
                'selling_price' => (float)$it['product']->selling_price,
                'line_total' => $it['line_total'],
                'image' => $it['product']->image_url,
            ]),
        ]);
    }

    public function add(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity'=>['nullable','numeric','min:1'],
            'size'=>['nullable','string','max:30'],
            'buy_now'=>['nullable','boolean'],
        ]);

        abort_unless($product->status, 404);
        $size = $this->validatedSize($product, $data['size'] ?? null);
        $qty = (float)($data['quantity'] ?? 1);
        $cart = $this->normalizeCart($request->session()->get('cart', []));
        $key = $this->cartKey($product->id, $size);

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
            'product_id'=>$product->id,
            'size'=>$size,
            'quantity'=>0,
        ];
        $existing['quantity'] = (float)$existing['quantity'] + $qty;
        $cart[$key] = $existing;
        $request->session()->put('cart', $cart);

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout');
        }

        if ($request->wantsJson() || $request->ajax()) {
            $data = $this->cartData($request);
            $totalCount = collect($cart)->sum(fn ($line) => is_array($line) ? (float)($line['quantity'] ?? 0) : (float)$line);
            return response()->json([
                'success' => true,
                'message' => $product->name.($size ? " ({$size})" : '').' added to cart.',
                'cart_count' => $totalCount,
                'subtotal' => $data['subtotal'],
                'total' => $data['total'],
                'items' => $data['items']->map(fn ($it) => [
                    'key' => $it['key'],
                    'product_id' => $it['product']->id,
                    'name' => $it['product']->name,
                    'size' => $it['size'],
                    'quantity' => $it['quantity'],
                    'selling_price' => (float)$it['product']->selling_price,
                    'line_total' => $it['line_total'],
                    'image' => $it['product']->image_url,
                ]),
            ]);
        }

        return back()->with('status', $product->name.($size ? " ({$size})" : '').' added to cart.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity'=>['required','numeric','min:0'],
            'size'=>['nullable','string','max:30'],
        ]);

        $size = $this->validatedSize($product, $data['size'] ?? null);
        $cart = $this->normalizeCart($request->session()->get('cart', []));
        $key = $this->cartKey($product->id, $size);
        $qty = (float)$data['quantity'];

        if ($qty <= 0) {
            unset($cart[$key]);
        } else {
            $otherQty = collect($cart)
                ->reject(fn ($line, $lineKey) => $lineKey === $key)
                ->where('product_id', $product->id)
                ->sum(fn ($line) => (float)$line['quantity']);

            if ($otherQty + $qty > $product->available_stock) {
                $msg = "Only {$product->available_stock} available across all sizes.";
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->withErrors(['cart' => $msg]);
            }

            $cart[$key] = [
                'product_id'=>$product->id,
                'size'=>$size,
                'quantity'=>$qty,
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

        return back()->with('status','Cart updated.');
    }

    public function remove(Request $request, Product $product)
    {
        $data = $request->validate(['size'=>['nullable','string','max:30']]);
        $size = $this->normalizedSize($data['size'] ?? null);
        $cart = $this->normalizeCart($request->session()->get('cart', []));
        unset($cart[$this->cartKey($product->id, $size)]);
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

        return back()->with('status','Product removed from cart.');
    }

    public function checkout(Request $request)
    {
        $data = $this->cartData($request);
        if ($data['items']->isEmpty()) {
            return redirect()->route('shop.index')->withErrors(['cart'=>'Your cart is empty.']);
        }
        return view('shop.checkout', $data);
    }

    private function cartData(Request $request): array
    {
        $cart = $this->normalizeCart($request->session()->get('cart', []));
        $request->session()->put('cart', $cart);

        $productIds = collect($cart)->pluck('product_id')->unique()->values()->all();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $items = collect($cart)->map(function ($line, $key) use ($products) {
            $product = $products->get((int)$line['product_id']);
            if (! $product) {
                return null;
            }

            $size = $product->has_sizes ? ($line['size'] ?? null) : null;
            if ($product->has_sizes && ! $product->hasSize($size)) {
                return null;
            }

            $quantity = (float)$line['quantity'];
            return [
                'key'=>$key,
                'product'=>$product,
                'size'=>$size,
                'quantity'=>$quantity,
                'line_total'=>$quantity*(float)$product->selling_price,
            ];
        })->filter()->values();

        $subtotal = (float)$items->sum('line_total');
        $deliveryFee = $subtotal > 0 ? 80.00 : 0;

        return compact('items','subtotal','deliveryFee') + ['total'=>$subtotal+$deliveryFee];
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
            throw ValidationException::withMessages([
                'size'=>'Please select a valid size for '.$product->name.'.',
            ]);
        }

        return collect($product->normalizedSizes())->first(
            fn ($option) => mb_strtolower($option) === mb_strtolower((string)$size)
        );
    }

    private function cartKey(int $productId, ?string $size): string
    {
        return $productId.':'.mb_strtolower($size ?: '_');
    }

    private function normalizeCart(array $cart): array
    {
        $normalized = [];

        foreach ($cart as $key => $value) {
            if (is_array($value)) {
                $productId = (int)($value['product_id'] ?? 0);
                $quantity = (float)($value['quantity'] ?? 0);
                $size = $this->normalizedSize($value['size'] ?? null);
            } else {
                $productId = (int)$key;
                $quantity = (float)$value;
                $size = null;
            }

            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            $lineKey = $this->cartKey($productId, $size);
            if (isset($normalized[$lineKey])) {
                $normalized[$lineKey]['quantity'] += $quantity;
            } else {
                $normalized[$lineKey] = [
                    'product_id'=>$productId,
                    'size'=>$size,
                    'quantity'=>$quantity,
                ];
            }
        }

        return $normalized;
    }
}
