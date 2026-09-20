<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Services\AccountingService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with('customer')->latest();
        if ($request->user()->role === 'cashier') {
            $query->where('created_by', $request->user()->id)->whereNull('online_order_id');
        }
        return view('sales.index',['sales'=>$query->paginate(20)]);
    }

    public function create()
    {
        return view('sales.create',[
            'customers'=>Customer::where('status',true)->orderBy('name')->get(),
            'products'=>Product::where('status',true)->whereRaw('(stock - reserved_stock) > 0')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, InventoryService $inventory, AccountingService $accounting)
    {
        $data = $request->validate([
            'customer_id'=>'nullable|exists:customers,id',
            'sale_date'=>'required|date',
            'discount'=>'nullable|numeric|min:0',
            'tax'=>'nullable|numeric|min:0',
            'paid_amount'=>'nullable|numeric|min:0',
            'payment_method'=>'required|in:cash,card,bank,bkash,nagad,credit',
            'notes'=>'nullable|string',
            'items'=>'required|array|min:1',
            'items.*.product_id'=>'required|exists:products,id',
            'items.*.selected_size'=>'nullable|string|max:30',
            'items.*.quantity'=>'required|numeric|min:0.01',
            'items.*.unit_price'=>'required|numeric|min:0',
        ]);

        $sale = DB::transaction(function () use ($data, $request, $inventory) {
            $subtotal = collect($data['items'])->sum(fn($i)=>(float)$i['quantity']*(float)$i['unit_price']);
            $total = max(0,$subtotal-(float)($data['discount']??0)+(float)($data['tax']??0));
            $paid = min($total,(float)($data['paid_amount']??$total));

            if (!($data['customer_id'] ?? null) && $paid < $total) {
                throw ValidationException::withMessages(['customer_id'=>'A customer is required for a credit sale.']);
            }

            $sale = Sale::create([
                'customer_id'=>$data['customer_id']??null,
                'invoice_no'=>'INV-'.now()->format('YmdHis').'-'.random_int(10,99),
                'sale_date'=>$data['sale_date'],
                'subtotal'=>$subtotal,
                'discount'=>$data['discount']??0,
                'tax'=>$data['tax']??0,
                'total'=>$total,
                'paid_amount'=>$paid,
                'due_amount'=>$total-$paid,
                'payment_method'=>$data['payment_method'],
                'status'=>'completed',
                'notes'=>$data['notes']??null,
                'created_by'=>$request->user()->id,
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $selectedSize = $this->canonicalSize($product, $item['selected_size'] ?? null);
                $line = (float)$item['quantity']*(float)$item['unit_price'];

                $sale->items()->create([
                    'product_id'=>$product->id,
                    'selected_size'=>$selectedSize,
                    'quantity'=>$item['quantity'],
                    'unit_price'=>$item['unit_price'],
                    'unit_cost'=>$product->purchase_price,
                    'line_total'=>$line,
                ]);

                $inventory->remove(
                    $product,
                    (float)$item['quantity'],
                    (float)$product->purchase_price,
                    'sale',
                    $sale->id,
                    $request->user()->id,
                    'In-house sale '.$sale->invoice_no.($selectedSize ? ' · Size '.$selectedSize : '')
                );
            }

            return $sale;
        });

        $sale->load('items');
        $accounting->postSale($sale);
        return redirect()->route('sales.show',$sale)->with('status','In-house sale completed successfully.');
    }

    public function show(Request $request, Sale $sale)
    {
        if ($request->user()->role === 'cashier' && $sale->created_by !== $request->user()->id) {
            abort(403);
        }
        $sale->load('customer','items.product','creator','onlineOrder');
        return view('sales.show',compact('sale'));
    }

    private function canonicalSize(Product $product, ?string $size): ?string
    {
        if (! $product->has_sizes) {
            return null;
        }

        $size = trim((string)$size);
        $match = collect($product->normalizedSizes())->first(
            fn ($option) => mb_strtolower($option) === mb_strtolower($size)
        );

        if (! $match) {
            throw ValidationException::withMessages([
                'items'=>'Please select a valid size for '.$product->name.'.',
            ]);
        }

        return $match;
    }
}
