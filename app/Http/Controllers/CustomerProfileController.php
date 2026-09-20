<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerProfileController extends Controller
{
    public function edit(Request $request)
    {
        $customer = Customer::firstOrCreate(['user_id'=>$request->user()->id], [
            'name'=>$request->user()->name,'email'=>$request->user()->email,'phone'=>$request->user()->phone,'customer_type'=>'retail','status'=>true,
        ]);
        return view('customer.profile', compact('customer'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name'=>['required','string','max:120'],
            'phone'=>['required','string','max:30'],
            'address'=>['nullable','string','max:600'],
        ]);
        DB::transaction(function () use ($request, $data) {
            $request->user()->update(['name'=>$data['name'],'phone'=>$data['phone']]);
            Customer::updateOrCreate(['user_id'=>$request->user()->id], [
                'name'=>$data['name'],'email'=>$request->user()->email,'phone'=>$data['phone'],'address'=>$data['address']??null,'customer_type'=>'retail','status'=>true,
            ]);
        });
        return back()->with('status','Profile updated.');
    }
}
