<?php
namespace App\Http\Controllers;
use App\Models\Supplier;
use Illuminate\Http\Request;
class SupplierController extends Controller
{
    public function index(){return view('contacts.suppliers',['suppliers'=>Supplier::latest()->paginate(20)]);}
    public function store(Request $r){$d=$r->validate(['name'=>'required|max:120','company'=>'nullable|max:120','phone'=>'nullable|max:30','email'=>'nullable|email|max:120','address'=>'nullable','credit_limit'=>'nullable|numeric|min:0']);$d['status']=true;Supplier::create($d);return back()->with('status','Supplier added.');}
    public function update(Request $r,Supplier $supplier){$d=$r->validate(['name'=>'required|max:120','company'=>'nullable|max:120','phone'=>'nullable|max:30','email'=>'nullable|email|max:120','address'=>'nullable','credit_limit'=>'nullable|numeric|min:0','status'=>'nullable|boolean']);$supplier->update($d);return back()->with('status','Supplier updated.');}
}
