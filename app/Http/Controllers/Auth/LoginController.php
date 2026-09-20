<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show() { return view('auth.login'); }
    public function login(Request $request) {
        $credentials = $request->validate(['email'=>['required','email'],'password'=>['required','string']]);
        $remember = $request->boolean('remember');
        if (Auth::attempt($credentials, $remember)) {
            if (! Auth::user()->is_active) { Auth::logout(); return back()->withErrors(['email'=>'This account is disabled.']); }
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }
        return back()->withErrors(['email'=>'Invalid email or password.'])->onlyInput('email');
    }
    public function logout(Request $request) {
        Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
