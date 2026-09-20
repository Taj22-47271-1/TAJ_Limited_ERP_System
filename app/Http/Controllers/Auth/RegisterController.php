<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function show() { return view('auth.register'); }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'=>['required','string','max:100'],
            'email'=>['required','email','max:150','unique:users,email'],
            'phone'=>['required','string','max:30'],
            'password'=>['required','confirmed','min:8'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name'=>$data['name'], 'email'=>$data['email'], 'phone'=>$data['phone'],
                'password'=>$data['password'], 'role'=>'customer', 'is_active'=>true,
            ]);
            Customer::create([
                'user_id'=>$user->id, 'name'=>$data['name'], 'email'=>$data['email'], 'phone'=>$data['phone'],
                'customer_type'=>'retail', 'status'=>true,
            ]);
            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('dashboard')->with('status', 'Welcome to TAJ Limited. Your customer account is ready.');
    }
}
