<?php
namespace App\Http\Controllers;
use App\Models\Expense;
use App\Services\AccountingService;
use Illuminate\Http\Request;
class ExpenseController extends Controller
{
    public function index(){return view('expenses.index',['expenses'=>Expense::latest('expense_date')->paginate(20)]);}
    public function store(Request $r,AccountingService $accounting){$d=$r->validate(['expense_date'=>'required|date','category'=>'required|max:80','description'=>'required|max:200','amount'=>'required|numeric|min:0.01','payment_method'=>'required|in:cash,bank,card,bkash,nagad','reference'=>'nullable|max:100']);$d['created_by']=$r->user()->id;$e=Expense::create($d);$accounting->postExpense($e);return back()->with('status','Expense recorded.');}
}
