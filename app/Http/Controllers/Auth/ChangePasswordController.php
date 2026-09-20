<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class ChangePasswordController extends Controller
{
    public function edit() { return view('profile.change-password'); }
    public function update(Request $request) {
        $data=$request->validate(['current_password'=>['required'],'password'=>['required','confirmed','min:8']]);
        if (! Hash::check($data['current_password'],$request->user()->password)) return back()->withErrors(['current_password'=>'Current password is incorrect.']);
        $request->user()->update(['password'=>Hash::make($data['password'])]);
        return back()->with('status','Password changed successfully.');
    }
}
