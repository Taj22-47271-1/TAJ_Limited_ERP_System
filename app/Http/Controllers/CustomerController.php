<?php
namespace App\Http\Controllers;
use App\Models\Customer;
use Illuminate\Http\Request;
class CustomerController extends Controller
{
    public function index(){return view('contacts.customers',['customers'=>Customer::latest()->paginate(20)]);}
    public function store(Request $r){$d=$r->validate(['name'=>'required|max:120','phone'=>'nullable|max:30','email'=>'nullable|email|max:120','address'=>'nullable','customer_type'=>'required|in:retail,wholesale','credit_limit'=>'nullable|numeric|min:0']);$d['status']=true;Customer::create($d);return back()->with('status','Customer added.');}
    public function update(Request $r,Customer $customer){$d=$r->validate(['name'=>'required|max:120','phone'=>'nullable|max:30','email'=>'nullable|email|max:120','address'=>'nullable','customer_type'=>'required|in:retail,wholesale','credit_limit'=>'nullable|numeric|min:0','status'=>'nullable|boolean']);$customer->update($d);return back()->with('status','Customer updated.');}
}
