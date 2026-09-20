<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function showForgot() { return view('auth.forgot-password'); }

    public function sendOtp(Request $request) {
        $data=$request->validate(['email'=>['required','email','exists:users,email']]);
        $recent=PasswordResetOtp::where('email',$data['email'])->latest()->first();
        if ($recent && $recent->created_at->gt(now()->subMinute())) return back()->withErrors(['email'=>'Please wait 60 seconds before requesting another OTP.']);

        $otp=(string) random_int(100000,999999);
        PasswordResetOtp::where('email',$data['email'])->delete();
        PasswordResetOtp::create(['email'=>$data['email'],'otp_hash'=>Hash::make($otp),'expires_at'=>now()->addMinutes(10),'attempts'=>0]);
        Mail::to($data['email'])->send(new PasswordResetOtpMail($otp));
        $request->session()->put('password_reset_email',$data['email']);
        return redirect()->route('password.otp.form')->with('status','A 6-digit OTP has been sent to your email.');
    }

    public function showVerify(Request $request) {
        abort_unless($request->session()->has('password_reset_email'), 403);
        return view('auth.verify-otp',['email'=>$request->session()->get('password_reset_email')]);
    }

    public function verifyOtp(Request $request) {
        $data=$request->validate(['otp'=>['required','digits:6']]);
        $email=$request->session()->get('password_reset_email');
        abort_unless($email,403);
        $record=PasswordResetOtp::where('email',$email)->latest()->first();
        if (! $record || $record->expires_at->isPast()) return back()->withErrors(['otp'=>'OTP expired. Request a new code.']);
        if ($record->attempts >= 5) return back()->withErrors(['otp'=>'Too many attempts. Request a new OTP.']);
        $record->increment('attempts');
        if (! Hash::check($data['otp'],$record->otp_hash)) return back()->withErrors(['otp'=>'Incorrect OTP.']);
        $record->update(['verified_at'=>now()]);
        $request->session()->put('password_reset_verified_email',$email);
        return redirect()->route('password.reset.form');
    }

    public function showReset(Request $request) {
        abort_unless($request->session()->has('password_reset_verified_email'),403);
        return view('auth.reset-password');
    }

    public function reset(Request $request) {
        $email=$request->session()->get('password_reset_verified_email'); abort_unless($email,403);
        $data=$request->validate(['password'=>['required','confirmed','min:8']]);
        User::where('email',$email)->update(['password'=>Hash::make($data['password'])]);
        PasswordResetOtp::where('email',$email)->delete();
        $request->session()->forget(['password_reset_email','password_reset_verified_email']);
        return redirect()->route('login')->with('status','Password reset successfully. Please sign in.');
    }
}
