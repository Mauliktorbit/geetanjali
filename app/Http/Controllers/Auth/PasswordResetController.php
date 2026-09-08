<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PasswordOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

class PasswordResetController extends Controller
{
    public function __construct(private readonly PasswordOtpService $otps) {}

    public function showEmailForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower($data['email']);
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('is_staff', false)
            ->first();

        if (! $user) {
            return back()
                ->withInput(['email' => $email])
                ->withErrors(['email' => 'No customer account was found for this email.']);
        }

        if (! $user->is_active) {
            return back()
                ->withInput(['email' => $email])
                ->withErrors(['email' => 'This account is inactive. Please contact support.']);
        }

        if ($this->otps->tooSoon($user->email)) {
            return back()
                ->withInput(['email' => $email])
                ->withErrors(['email' => 'Please wait a minute before requesting another OTP.']);
        }

        try {
            $this->otps->send($user);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput(['email' => $email])
                ->withErrors(['email' => 'We could not send the OTP right now. Please try again in a moment.']);
        }

        $request->session()->put('password_reset_email', $user->email);
        $request->session()->forget('password_otp_verified');

        return redirect()
            ->route('password.otp')
            ->with('success', 'We have sent a 6-digit OTP to your email. It is valid for '.PasswordOtpService::TTL_MINUTES.' minutes.');
    }

    public function showOtpForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->get('password_reset_email')) {
            return redirect()->route('password.request');
        }

        $email = $request->session()->get('password_reset_email');

        return view('auth.verify-otp', [
            'email' => $email,
            'maskedEmail' => $this->maskEmail($email),
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');
        if (! $email) {
            return redirect()->route('password.request');
        }

        $data = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $status = $this->otps->verify($email, $data['otp']);

        return match ($status) {
            'ok' => $this->markVerified($request),
            'expired' => back()->withErrors(['otp' => 'This OTP has expired. Please request a new one.']),
            'locked' => back()->withErrors(['otp' => 'Too many incorrect attempts. Please request a new OTP.']),
            default => back()->withErrors(['otp' => 'The OTP you entered is incorrect.']),
        };
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');
        if (! $email) {
            return redirect()->route('password.request');
        }

        $user = User::query()->where('email', $email)->where('is_staff', false)->first();
        if (! $user) {
            return redirect()->route('password.request');
        }

        if ($this->otps->tooSoon($email)) {
            return back()->withErrors(['otp' => 'Please wait a minute before requesting another OTP.']);
        }

        try {
            $this->otps->send($user);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['otp' => 'We could not resend the OTP right now. Please try again.']);
        }

        $request->session()->forget('password_otp_verified');

        return back()->with('success', 'A new OTP has been sent to your email.');
    }

    public function showResetForm(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');
        if (! $email || ! $request->session()->get('password_otp_verified')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password', [
            'email' => $email,
            'maskedEmail' => $this->maskEmail($email),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');
        if (! $email || ! $request->session()->get('password_otp_verified') || ! $this->otps->isVerified($email)) {
            return redirect()->route('password.request')->withErrors(['email' => 'Please verify your OTP again.']);
        }

        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::query()->where('email', $email)->where('is_staff', false)->firstOrFail();
        $user->update(['password' => $data['password']]);

        $this->otps->clear($email);
        $request->session()->forget(['password_reset_email', 'password_otp_verified']);

        return redirect()->route('login')->with('success', 'Your password has been updated. Please sign in.');
    }

    private function markVerified(Request $request): RedirectResponse
    {
        $request->session()->put('password_otp_verified', true);

        return redirect()->route('password.reset')->with('success', 'OTP verified. Please set a new password.');
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $keep = min(2, strlen($local));

        return substr($local, 0, $keep).str_repeat('*', max(strlen($local) - $keep, 1)).'@'.$domain;
    }
}
