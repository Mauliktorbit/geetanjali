<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\LoginRequest;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends AdminController
{
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->is_staff) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(LoginRequest $request)
    {
        $email = $request->validated('email');
        $password = $request->validated('password');
        $ip = $request->ip();
        $userAgent = (string) $request->userAgent();

        /** @var User|null $user */
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->logAttempt(null, $email, false, $ip, $userAgent, 'User not found');

            return $this->error('Invalid credentials.');
        }

        if (! $user->is_staff) {
            $this->logAttempt($user->id, $email, false, $ip, $userAgent, 'Not staff');

            return $this->error('Invalid credentials.');
        }

        if (! $user->is_active) {
            $this->logAttempt($user->id, $email, false, $ip, $userAgent, 'Inactive account');

            return $this->error('Your account is inactive.');
        }

        if ($user->isLocked()) {
            $this->logAttempt($user->id, $email, false, $ip, $userAgent, 'Account locked');

            return $this->error('Account locked until ' . $user->locked_until->toDateTimeString());
        }

        if (! empty($user->allowed_ips) && ! in_array($ip, $user->allowed_ips, true)) {
            $this->logAttempt($user->id, $email, false, $ip, $userAgent, 'IP not allowed');

            return $this->error('Login not allowed from this IP address.');
        }

        if (! Hash::check($password, $user->password)) {
            $attempts = (int) $user->failed_login_attempts + 1;
            $updates = ['failed_login_attempts' => $attempts];
            if ($attempts >= 5) {
                $updates['locked_until'] = now()->addMinutes(30);
            }
            $user->update($updates);
            $this->logAttempt($user->id, $email, false, $ip, $userAgent, 'Invalid password');

            return $this->error('Invalid credentials.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);

        $this->logAttempt($user->id, $email, true, $ip, $userAgent);

        return redirect()->intended(route('admin.dashboard'))
            ->with('success', 'Welcome back, ' . $user->name);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }

    protected function logAttempt(
        ?int $userId,
        string $email,
        bool $successful,
        string $ip,
        string $userAgent,
        ?string $failureReason = null
    ): void {
        LoginHistory::create([
            'user_id' => $userId,
            'email' => $email,
            'successful' => $successful,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'failure_reason' => $failureReason,
        ]);
    }
}
