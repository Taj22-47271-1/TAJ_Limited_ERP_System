<?php
namespace App\Http\Controllers;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
class ReportController extends Controller
{
    public function profitLoss(Request $r){$from=$r->input('from',now()->startOfMonth()->toDateString());$to=$r->input('to',now()->toDateString());$revenue=(float)Sale::whereBetween('sale_date',[$from,$to])->sum('total');$cogs=(float)SaleItem::whereHas('sale',fn($q)=>$q->whereBetween('sale_date',[$from,$to]))->selectRaw('COALESCE(SUM(quantity*unit_cost),0) x')->value('x');$expenses=(float)Expense::whereBetween('expense_date',[$from,$to])->sum('amount');$gross=$revenue-$cogs;$net=$gross-$expenses;return view('reports.profit-loss',compact('from','to','revenue','cogs','expenses','gross','net'));}
    public function salesCsv(Request $r): StreamedResponse { $from=$r->input('from',now()->startOfMonth()->toDateString());$to=$r->input('to',now()->toDateString());$rows=Sale::with('customer')->whereBetween('sale_date',[$from,$to])->orderBy('sale_date')->get();return response()->streamDownload(function() use($rows){$o=fopen('php://output','w');fputcsv($o,['Invoice','Date','Customer','Total','Paid','Due','Payment']);foreach($rows as $s)fputcsv($o,[$s->invoice_no,$s->sale_date->toDateString(),$s->customer?->name??'Walk-in',$s->total,$s->paid_amount,$s->due_amount,$s->payment_method]);fclose($o);},"sales-{$from}-{$to}.csv",['Content-Type'=>'text/csv']); }
}
