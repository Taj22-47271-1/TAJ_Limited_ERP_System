<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Services\OtpMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class ForgotPasswordController extends Controller
{
    public function showForgot() { return view('auth.forgot-password'); }

    public function sendOtp(Request $request) {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email']
        ], [
            'email.exists' => 'No account found with this email address.'
        ]);

        $recent = PasswordResetOtp::where('email', $data['email'])->latest()->first();
        if ($recent && $recent->created_at->gt(now()->subMinute())) {
            $secondsLeft = max(1, 60 - now()->diffInSeconds($recent->created_at));
            return back()->withInput()->withErrors(['email' => "Please wait {$secondsLeft} seconds before requesting another OTP."]);
        }

        $otp = (string) random_int(100000, 999999);

        // Attempt dispatch via HTTPS API (Brevo / Resend) or SMTP
        $mailSent = OtpMailService::sendOtp($data['email'], $otp);

        if (! $mailSent) {
            Log::error("Password reset OTP email failed to dispatch for {$data['email']}. Check outbound SMTP or REST API configuration.");
            
            // Clean up any stale OTP records so no invalid or unreceived OTPs linger
            PasswordResetOtp::where('email', $data['email'])->delete();

            return back()->withInput()->withErrors([
                'email' => 'Unable to deliver verification email at this moment. Please verify your mail settings or try again in a few moments.'
            ]);
        }

        // Only persist OTP in database when email delivery has succeeded
        PasswordResetOtp::where('email', $data['email'])->delete();
        PasswordResetOtp::create([
            'email' => $data['email'],
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(15),
            'attempts' => 0
        ]);

        $request->session()->put('password_reset_email', $data['email']);

        return redirect()->route('password.otp.form')->with('status', 'A 6-digit verification code has been sent to your email inbox.');
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
