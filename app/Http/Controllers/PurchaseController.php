<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\AccountingService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PurchaseController extends Controller
{
    public function index(){return view('purchases.index',['purchases'=>Purchase::with('supplier')->latest()->paginate(20)]);}
    public function create(){return view('purchases.create',['suppliers'=>Supplier::where('status',true)->orderBy('name')->get(),'products'=>Product::where('status',true)->orderBy('name')->get()]);}
    public function store(Request $r,InventoryService $inventory,AccountingService $accounting){
        $d=$r->validate(['supplier_id'=>'required|exists:suppliers,id','purchase_date'=>'required|date','discount'=>'nullable|numeric|min:0','tax'=>'nullable|numeric|min:0','paid_amount'=>'nullable|numeric|min:0','notes'=>'nullable|string','items'=>'required|array|min:1','items.*.product_id'=>'required|exists:products,id','items.*.quantity'=>'required|numeric|min:0.01','items.*.unit_cost'=>'required|numeric|min:0']);
        $purchase=DB::transaction(function() use($d,$r,$inventory){
            $subtotal=collect($d['items'])->sum(fn($i)=>(float)$i['quantity']*(float)$i['unit_cost']);$total=max(0,$subtotal-(float)($d['discount']??0)+(float)($d['tax']??0));$paid=min($total,(float)($d['paid_amount']??0));
            $p=Purchase::create(['supplier_id'=>$d['supplier_id'],'invoice_no'=>'PO-'.now()->format('YmdHis').'-'.random_int(10,99),'purchase_date'=>$d['purchase_date'],'subtotal'=>$subtotal,'discount'=>$d['discount']??0,'tax'=>$d['tax']??0,'total'=>$total,'paid_amount'=>$paid,'due_amount'=>$total-$paid,'status'=>'received','notes'=>$d['notes']??null,'created_by'=>$r->user()->id]);
            foreach($d['items'] as $i){$product=Product::lockForUpdate()->findOrFail($i['product_id']);$line=(float)$i['quantity']*(float)$i['unit_cost'];$p->items()->create(['product_id'=>$product->id,'quantity'=>$i['quantity'],'unit_cost'=>$i['unit_cost'],'line_total'=>$line]);$product->update(['purchase_price'=>$i['unit_cost']]);$inventory->add($product,(float)$i['quantity'],(float)$i['unit_cost'],'purchase',$p->id,$r->user()->id,'Purchase '.$p->invoice_no);} return $p;
        });
        $purchase->load('items');$accounting->postPurchase($purchase);return redirect()->route('purchases.show',$purchase)->with('status','Purchase received and stock updated.');
    }
    public function show(Purchase $purchase){$purchase->load('supplier','items.product','creator');return view('purchases.show',compact('purchase'));}
}
