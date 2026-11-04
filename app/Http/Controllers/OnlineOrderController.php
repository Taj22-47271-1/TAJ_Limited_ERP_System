<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\OnlineOrder;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Sale;
use App\Services\AccountingService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OnlineOrderController extends Controller
{
    public function store(Request $request, InventoryService $inventory)
    {
        $data = $request->validate([
            'full_name'=>['required','string','max:120'],
            'email'=>['required','email','max:150'],
            'phone'=>['required','string','max:30'],
            'address'=>['required','string','max:600'],
            'city'=>['nullable','string','max:100'],
            'area'=>['nullable','string','max:100'],
            'postal_code'=>['nullable','string','max:20'],
            'notes'=>['nullable','string','max:1000'],
            'payment_method'=>['required','in:cod,online'],
        ]);

        $cart = $this->normalizeCart($request->session()->get('cart', []));
        if (empty($cart)) {
            return redirect()->route('shop.index')->withErrors(['cart'=>'Your cart is empty.']);
        }

        $order = DB::transaction(function () use ($request, $data, $cart, $inventory) {
            $productIds = collect($cart)->pluck('product_id')->unique()->values()->all();
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');
            $subtotal = 0.0;

            foreach ($cart as $line) {
                $product = $products->get((int)$line['product_id']);
                if (! $product || ! $product->status) {
                    throw ValidationException::withMessages(['cart'=>'A product in your cart is no longer available.']);
                }

                $this->assertValidSize($product, $line['size'] ?? null);
                $qty = (float)$line['quantity'];
                $inventory->reserve($product, $qty);
                $subtotal += $qty * (float)$product->selling_price;
            }

            $deliveryFee = $subtotal > 0 ? 80.00 : 0.00;
            $order = OnlineOrder::create([
                'user_id'=>$request->user()->id,
                'order_no'=>'ORD-'.now()->format('YmdHis').'-'.random_int(100,999),
                'full_name'=>$data['full_name'],
                'email'=>$data['email'],
                'phone'=>$data['phone'],
                'address'=>$data['address'],
                'city'=>$data['city']??null,
                'area'=>$data['area']??null,
                'postal_code'=>$data['postal_code']??null,
                'notes'=>$data['notes']??null,
                'subtotal'=>$subtotal,
                'delivery_fee'=>$deliveryFee,
                'total'=>$subtotal+$deliveryFee,
                'payment_method'=>$data['payment_method'],
                'payment_status'=>$data['payment_method']==='cod'?'unpaid':'pending',
                'order_status'=>'pending',
            ]);

            foreach ($cart as $line) {
                $product = $products->get((int)$line['product_id']);
                $qty = (float)$line['quantity'];
                $size = $product->has_sizes ? $this->canonicalSize($product, $line['size'] ?? null) : null;

                $order->items()->create([
                    'product_id'=>$product->id,
                    'product_name'=>$product->name,
                    'selected_size'=>$size,
                    'quantity'=>$qty,
                    'unit_price'=>$product->selling_price,
                    'unit_cost'=>$product->purchase_price,
                    'line_total'=>$qty*(float)$product->selling_price,
                ]);
            }

            OrderStatusHistory::create([
                'online_order_id'=>$order->id,
                'status'=>'pending',
                'note'=>'Order placed and waiting for approval.',
                'changed_by'=>$request->user()->id,
            ]);

            $customer = Customer::firstOrNew(['user_id'=>$request->user()->id]);
            $customer->fill([
                'name'=>$data['full_name'],
                'email'=>$data['email'],
                'phone'=>$data['phone'],
                'address'=>$data['address'],
                'customer_type'=>'retail',
                'status'=>true,
            ]);
            $customer->save();
            $request->user()->update(['name'=>$data['full_name'],'phone'=>$data['phone']]);

            return $order;
        });

        $request->session()->forget('cart');
        if ($order->payment_method === 'online') {
            return redirect()->route('customer.orders.pay', $order);
        }

        return redirect()->route('customer.orders.show', $order)
            ->with('status','Order placed successfully. It is waiting for approval.');
    }

    public function customerIndex(Request $request)
    {
        return view('customer.orders', [
            'orders'=>OnlineOrder::where('user_id',$request->user()->id)->latest()->paginate(12),
        ]);
    }

    public function customerShow(Request $request, OnlineOrder $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        $order->load('items.product','histories.changer');
        return view('customer.order-show', compact('order'));
    }

    public function cancel(Request $request, OnlineOrder $order, InventoryService $inventory)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        DB::transaction(function () use ($order, $request, $inventory) {
            $locked = OnlineOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->order_status !== 'pending') {
                throw ValidationException::withMessages(['order'=>'Only pending orders can be cancelled by the customer.']);
            }

            $locked->load('items');
            foreach ($locked->items as $item) {
                $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();
                $inventory->releaseReservation($product, (float)$item->quantity);
            }

            $locked->update([
                'order_status'=>'cancelled',
                'payment_status'=>$locked->payment_status==='paid'?'refund_required':$locked->payment_status,
            ]);

            OrderStatusHistory::create([
                'online_order_id'=>$locked->id,
                'status'=>'cancelled',
                'note'=>'Cancelled by customer.',
                'changed_by'=>$request->user()->id,
            ]);
        });

        return redirect()->route('customer.orders')->with('status','Order cancelled and reserved stock released.');
    }

    public function staffIndex(Request $request)
    {
        $query = OnlineOrder::with('user')->latest();
        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }
        if ($request->filled('q')) {
            $term = trim($request->q);
            $query->where(fn($q) => $q->where('order_no','like',"%{$term}%")
                ->orWhere('full_name','like',"%{$term}%")
                ->orWhere('phone','like',"%{$term}%"));
        }
        return view('orders.index', ['orders'=>$query->paginate(20)->withQueryString()]);
    }

    public function staffShow(OnlineOrder $order)
    {
        $order->load('items.product','histories.changer','user','approver');
        return view('orders.show', compact('order'));
    }

    public function approve(Request $request, OnlineOrder $order, InventoryService $inventory, AccountingService $accounting)
    {
        DB::transaction(function () use ($request, $order, $inventory, $accounting) {
            $locked = OnlineOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->order_status !== 'pending') {
                throw ValidationException::withMessages(['order'=>'Only pending orders can be approved.']);
            }

            // If payment was online and not yet paid, mark paid on staff approval
            if ($locked->payment_method === 'online' && $locked->payment_status !== 'paid') {
                $locked->payment_status = 'paid';
                if (! $locked->payment_reference) {
                    $locked->payment_reference = 'VERIFIED-BY-'.strtoupper($request->user()->name ?? 'STAFF');
                }
                $locked->save();
            }

            $locked->load('items');
            foreach ($locked->items as $item) {
                $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();
                $inventory->commitReserved(
                    $product,
                    (float)$item->quantity,
                    (float)$item->unit_cost,
                    'online_order',
                    $locked->id,
                    $request->user()->id,
                    'Online order '.$locked->order_no.($item->selected_size ? ' · Size '.$item->selected_size : '')
                );
            }

            $customer = Customer::firstOrCreate(['user_id'=>$locked->user_id], [
                'name'=>$locked->full_name,
                'email'=>$locked->email,
                'phone'=>$locked->phone,
                'address'=>$locked->address,
                'customer_type'=>'retail',
                'status'=>true,
            ]);

            $paid = $locked->payment_status === 'paid' ? (float)$locked->total : 0.0;
            $sale = Sale::create([
                'customer_id'=>$customer->id,
                'online_order_id'=>$locked->id,
                'invoice_no'=>'WEB-'.now()->format('YmdHis').'-'.random_int(10,99),
                'sale_date'=>now()->toDateString(),
                'subtotal'=>$locked->subtotal,
                'discount'=>0,
                'tax'=>$locked->delivery_fee,
                'total'=>$locked->total,
                'paid_amount'=>$paid,
                'due_amount'=>(float)$locked->total-$paid,
                'payment_method'=>$locked->payment_method==='online'?($locked->payment_channel ?: 'online'):'cash',
                'status'=>'completed',
                'notes'=>'Online order '.$locked->order_no,
                'created_by'=>$request->user()->id,
            ]);

            foreach ($locked->items as $item) {
                $sale->items()->create([
                    'product_id'=>$item->product_id,
                    'selected_size'=>$item->selected_size,
                    'quantity'=>$item->quantity,
                    'unit_price'=>$item->unit_price,
                    'unit_cost'=>$item->unit_cost,
                    'line_total'=>$item->line_total,
                ]);
            }

            $accounting->postSale($sale);
            $locked->update([
                'order_status'=>'approved',
                'approved_by'=>$request->user()->id,
                'approved_at'=>now(),
                'stock_committed_at'=>now(),
            ]);

            OrderStatusHistory::create([
                'online_order_id'=>$locked->id,
                'status'=>'approved',
                'note'=>'Order approved and stock committed.',
                'changed_by'=>$request->user()->id,
            ]);
        });

        return back()->with('status','Order approved. Customer can now track processing.');
    }

    public function progress(Request $request, OnlineOrder $order, AccountingService $accounting)
    {
        $nextMap = [
            'approved'=>'processing',
            'processing'=>'packed',
            'packed'=>'shipped',
            'shipped'=>'out_for_delivery',
            'out_for_delivery'=>'delivered',
        ];

        DB::transaction(function () use ($request, $order, $accounting, $nextMap) {
            $locked = OnlineOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $next = $nextMap[$locked->order_status] ?? null;
            if (! $next) {
                throw ValidationException::withMessages(['order'=>'This order cannot move to another processing stage.']);
            }

            if ($next === 'delivered' && $locked->payment_method === 'cod' && $locked->payment_status !== 'paid') {
                $sale = Sale::where('online_order_id', $locked->id)->first();
                if ($sale && (float)$sale->due_amount > 0) {
                    $amount = (float)$sale->due_amount;
                    $accounting->postReceivableCollection($sale, $amount, $request->user()->id);
                    $sale->update(['paid_amount'=>(float)$sale->total,'due_amount'=>0]);
                }
                $locked->payment_status = 'paid';
                $locked->payment_reference = 'COD-'.now()->format('YmdHis');
            }

            $locked->order_status = $next;
            $locked->save();

            OrderStatusHistory::create([
                'online_order_id'=>$locked->id,
                'status'=>$next,
                'note'=>'Order status updated to '.str_replace('_',' ', $next).'.',
                'changed_by'=>$request->user()->id,
            ]);
        });

        return back()->with('status','Order progress updated.');
    }

    public function reject(Request $request, OnlineOrder $order, InventoryService $inventory)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate(['reason'=>['nullable','string','max:500']]);

        DB::transaction(function () use ($request, $order, $inventory, $data) {
            $locked = OnlineOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->order_status !== 'pending') {
                throw ValidationException::withMessages(['order'=>'Only pending orders can be rejected.']);
            }

            $locked->load('items');
            foreach ($locked->items as $item) {
                $product = Product::whereKey($item->product_id)->lockForUpdate()->firstOrFail();
                $inventory->releaseReservation($product, (float)$item->quantity);
            }

            $locked->update([
                'order_status'=>'rejected',
                'payment_status'=>$locked->payment_status==='paid'?'refund_required':$locked->payment_status,
            ]);

            OrderStatusHistory::create([
                'online_order_id'=>$locked->id,
                'status'=>'rejected',
                'note'=>$data['reason'] ?: 'Rejected by administrator.',
                'changed_by'=>$request->user()->id,
            ]);
        });

        return back()->with('status','Order rejected and reserved stock released.');
    }

    private function normalizeCart(array $cart): array
    {
        $normalized = [];

        foreach ($cart as $key => $value) {
            if (is_array($value)) {
                $productId = (int)($value['product_id'] ?? 0);
                $quantity = (float)($value['quantity'] ?? 0);
                $size = trim((string)($value['size'] ?? '')) ?: null;
            } else {
                $productId = (int)$key;
                $quantity = (float)$value;
                $size = null;
            }

            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            $normalized[] = [
                'product_id'=>$productId,
                'size'=>$size,
                'quantity'=>$quantity,
            ];
        }

        return $normalized;
    }

    private function assertValidSize(Product $product, ?string $size): void
    {
        if ($product->has_sizes && ! $product->hasSize($size)) {
            throw ValidationException::withMessages([
                'cart'=>'Please select a valid size for '.$product->name.'.',
            ]);
        }
    }

    private function canonicalSize(Product $product, ?string $size): ?string
    {
        if (! $product->has_sizes) {
            return null;
        }

        $size = trim((string)$size);
        return collect($product->normalizedSizes())->first(
            fn ($option) => mb_strtolower($option) === mb_strtolower($size)
        );
    }
}
