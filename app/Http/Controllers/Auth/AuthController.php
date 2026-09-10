<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login', [
            'image' => 'public/assets/images/auth/login-jewellery.jpg',
            'imageAlt' => 'Gold Kundan earrings on ivory silk',
        ]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $email = (string) $credentials['email'];
        $user = User::query()->where('email', $email)->first();

        if ($user?->is_staff && $user->isLocked()) {
            $this->logStaffAttempt($user->id, $email, false, $request, 'Account locked');

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Your account is temporarily locked. Please try again later.']);
        }

        if (! Auth::attempt($credentials, true)) {
            if ($user?->is_staff) {
                $attempts = (int) $user->failed_login_attempts + 1;
                $updates = ['failed_login_attempts' => $attempts];
                if ($attempts >= 5) {
                    $updates['locked_until'] = now()->addMinutes(30);
                }
                $user->update($updates);
                $this->logStaffAttempt($user->id, $email, false, $request, 'Invalid password');
            }

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid email or password.']);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Your account is inactive. Please contact support.']);
        }

        if ($user->isLocked()) {
            Auth::logout();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Your account is temporarily locked. Please try again later.']);
        }

        if ($user->is_staff && ! empty($user->allowed_ips) && ! in_array($request->ip(), $user->allowed_ips, true)) {
            Auth::logout();
            $this->logStaffAttempt($user->id, $email, false, $request, 'IP not allowed');

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Login is not allowed from this IP address.']);
        }

        $guestCart = $request->session()->get('cart');
        $guestWishlist = $request->session()->get('wishlist');
        $guestCoupon = $request->session()->get('cart_coupon');
        $guestGift = $request->session()->get('cart_gift_message');

        $request->session()->regenerate();
        Auth::login($user, true);

        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $this->mergeGuestBags($user, $guestCart, $guestWishlist, $guestCoupon, $guestGift);

        if ($user->is_staff) {
            $this->logStaffAttempt($user->id, $email, true, $request);
        }

        return $this->redirectAfterLogin($user);
    }

    public function showRegister(): View
    {
        return view('auth.register', [
            'image' => 'public/assets/images/auth/register-jewellery.jpg',
            'imageAlt' => 'Premium Kundan necklace on champagne silk',
        ]);
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'mobile' => $request->validated('mobile'),
            'phone' => $request->validated('mobile'),
            'password' => $request->validated('password'),
            'is_staff' => false,
            'is_active' => true,
        ]);

        $guestCart = $request->session()->get('cart');
        $guestWishlist = $request->session()->get('wishlist');
        $guestCoupon = $request->session()->get('cart_coupon');
        $guestGift = $request->session()->get('cart_gift_message');

        $request->session()->regenerate();
        Auth::login($user, true);

        app(\App\Services\CheckoutService::class)->ensureCustomer($user);
        $this->mergeGuestBags($user, $guestCart, $guestWishlist, $guestCoupon, $guestGift);

        return redirect()->intended(route('account.index'))
            ->with('success', 'Your Geetanjali account has been created successfully.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }

    protected function redirectAfterLogin(User $user): RedirectResponse
    {
        if ($user->is_staff) {
            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Welcome back, '.$user->name);
        }

        return redirect()->intended(route('account.index'))
            ->with('success', 'Welcome back to Geetanjali Jewellers.');
    }

    private function mergeGuestBags(
        User $user,
        mixed $guestCart,
        mixed $guestWishlist,
        mixed $guestCoupon,
        mixed $guestGift,
    ): void {
        if ($user->is_staff) {
            return;
        }

        app(\App\Services\CartService::class)->importGuest($guestCart, $guestCoupon, $guestGift);
        app(\App\Services\WishlistService::class)->importGuest($guestWishlist);
    }

    private function logStaffAttempt(
        ?int $userId,
        string $email,
        bool $successful,
        Request $request,
        ?string $failureReason = null,
    ): void {
        LoginHistory::create([
            'user_id' => $userId,
            'email' => $email,
            'successful' => $successful,
            'ip_address' => (string) $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'failure_reason' => $failureReason,
        ]);
    }
}
