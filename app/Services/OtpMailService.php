<?php

namespace App\Services;

use App\Mail\PasswordResetOtpMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OtpMailService
{
    /**
     * Send OTP verification code using HTTPS API (Brevo / Resend) or SMTP fallback.
     */
    public static function sendOtp(string $toEmail, string $otp): bool
    {
        $htmlContent = view('emails.password-reset-otp', ['otp' => $otp])->render();

        // 1. Check for Brevo (Sendinblue) HTTPS API Key (Port 443 - Works on Render Free Tier)
        $brevoKey = env('BREVO_API_KEY', config('services.brevo.key'));
        if (!empty($brevoKey)) {
            try {
                $senderEmail = config('mail.from.address', 'projectw073@gmail.com');
                $senderName = config('mail.from.name', 'TAJ Limited');

                $res = Http::timeout(8)->withHeaders([
                    'api-key' => $brevoKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->post('https://api.brevo.com/v3/smtp/email', [
                    'sender' => ['name' => $senderName, 'email' => $senderEmail],
                    'to' => [['email' => $toEmail]],
                    'subject' => 'TAJ ERP Verification Code',
                    'htmlContent' => $htmlContent,
                ]);

                if ($res->successful()) {
                    Log::info("OTP successfully sent to {$toEmail} via Brevo HTTPS API");
                    return true;
                }
                Log::warning("Brevo HTTPS API delivery notice: " . $res->body());
            } catch (Throwable $e) {
                Log::warning("Brevo HTTPS API exception: " . $e->getMessage());
            }
        }

        // 2. Check for Resend HTTPS API Key (Port 443 - Works on Render Free Tier)
        $resendKey = env('RESEND_API_KEY', config('services.resend.key'));
        if (!empty($resendKey)) {
            try {
                $from = env('RESEND_FROM', 'TAJ Limited <onboarding@resend.dev>');

                $res = Http::timeout(8)->withToken($resendKey)->post('https://api.resend.com/emails', [
                    'from' => $from,
                    'to' => [$toEmail],
                    'subject' => 'TAJ ERP Verification Code',
                    'html' => $htmlContent,
                ]);

                if ($res->successful()) {
                    Log::info("OTP successfully sent to {$toEmail} via Resend HTTPS API");
                    return true;
                }
                Log::warning("Resend HTTPS API delivery notice: " . $res->body());
            } catch (Throwable $e) {
                Log::warning("Resend HTTPS API exception: " . $e->getMessage());
            }
        }

        // 3. Primary SMTP (Port 587 TLS) - Fast timeout (5s) to prevent hanging on blocked firewalls
        try {
            Mail::to($toEmail)->send(new PasswordResetOtpMail($otp));
            Log::info("OTP successfully sent to {$toEmail} via primary SMTP (587 TLS)");
            return true;
        } catch (Throwable $e) {
            Log::warning("Primary SMTP (587 TLS) delivery notice: " . $e->getMessage());

            // 4. Backup SMTP (Port 465 SSL)
            try {
                $backupMailer = app('mail.manager')->build([
                    'transport' => 'smtp',
                    'host' => config('mail.mailers.smtp.host', 'smtp.gmail.com'),
                    'port' => 465,
                    'encryption' => 'ssl',
                    'username' => config('mail.mailers.smtp.username', 'projectw073@gmail.com'),
                    'password' => config('mail.mailers.smtp.password', 'mwpdfrmyyavfnhba'),
                    'timeout' => 5,
                ]);
                $backupMailer->to($toEmail)->send(new PasswordResetOtpMail($otp));
                Log::info("OTP successfully sent to {$toEmail} via backup SMTP (465 SSL)");
                return true;
            } catch (Throwable $e2) {
                Log::warning("Backup SMTP (465 SSL) delivery notice: " . $e2->getMessage());
            }
        }

        return false;
    }
}
