<?php
namespace App\Http\Controllers;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
class SettingsController extends Controller
{
    public function edit(){return view('admin.settings',['settings'=>Setting::firstOrCreate(['id'=>1],['company_name'=>'TAJ Limited','currency_symbol'=>'৳'])]);}
    public function update(Request $r){$d=$r->validate(['company_name'=>'required|max:120','company_email'=>'nullable|email|max:120','company_phone'=>'nullable|max:60','company_address'=>'nullable|max:500','currency_symbol'=>'required|max:10']);$s=Setting::firstOrCreate(['id'=>1]);$s->update($d);Cache::forget('company.settings');return back()->with('status','Company settings updated.');}
}
