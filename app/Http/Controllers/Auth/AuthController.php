<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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

    public function login(LoginRequest $request): RedirectResponse|JsonResponse
    {
        $credentials = $request->only('email', 'password');
        $email = (string) $credentials['email'];
        $user = User::query()->where('email', $email)->first();

        if ($user?->is_staff && $user->isLocked()) {
            $this->logStaffAttempt($user->id, $email, false, $request, 'Account locked');

            return $this->loginFailure($request, 'Your account is temporarily locked. Please try again later.');
        }

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            if ($user?->is_staff) {
                $attempts = (int) $user->failed_login_attempts + 1;
                $updates = ['failed_login_attempts' => $attempts];
                if ($attempts >= 5) {
                    $updates['locked_until'] = now()->addMinutes(30);
                }
                $user->update($updates);
                $this->logStaffAttempt($user->id, $email, false, $request, 'Invalid password');
            }

            return $this->loginFailure($request, 'Invalid email or password.');
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_active || $user->customer?->is_blocked) {
            Auth::logout();

            return $this->loginFailure($request, 'Your account is inactive. Please contact support.');
        }

        if ($user->isLocked()) {
            Auth::logout();

            return $this->loginFailure($request, 'Your account is temporarily locked. Please try again later.');
        }

        if ($user->is_staff && ! empty($user->allowed_ips) && ! in_array($request->ip(), $user->allowed_ips, true)) {
            Auth::logout();
            $this->logStaffAttempt($user->id, $email, false, $request, 'IP not allowed');

            return $this->loginFailure($request, 'Login is not allowed from this IP address.');
        }

        $guestCart = $request->session()->get('cart');
        $guestWishlist = $request->session()->get('wishlist');
        $guestCoupon = $request->session()->get('cart_coupon');
        $guestGift = $request->session()->get('cart_gift_message');

        $request->session()->regenerate();
        Auth::login($user, $remember);
        $request->session()->put('auth.login_at', now()->timestamp);

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

        return $this->redirectAfterLogin($request, $user);
    }

    public function showRegister(): View
    {
        return view('auth.register', [
            'image' => 'public/assets/images/auth/register-jewellery.jpg',
            'imageAlt' => 'Premium Kundan necklace on champagne silk',
        ]);
    }

    public function checkRegisterField(Request $request): JsonResponse
    {
        $data = $request->validate([
            'field' => ['required', 'in:email,mobile'],
            'value' => ['nullable', 'string', 'max:255'],
        ]);

        if ($data['field'] === 'email') {
            $value = strtolower(trim((string) ($data['value'] ?? '')));
            $taken = $value !== '' && User::query()->where('email', $value)->exists();

            return response()->json([
                'taken' => $taken,
                'message' => $taken ? 'An account with this email already exists. Please sign in.' : null,
            ]);
        }

        $value = preg_replace('/\D+/', '', (string) ($data['value'] ?? '')) ?: '';
        $taken = $value !== '' && User::query()->where('mobile', $value)->exists();

        return response()->json([
            'taken' => $taken,
            'message' => $taken ? 'This mobile number is already registered.' : null,
        ]);
    }

    public function register(RegisterRequest $request): RedirectResponse|JsonResponse
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
        $request->session()->put('auth.login_at', now()->timestamp);
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $customer = app(\App\Services\CheckoutService::class)->ensureCustomer($user);
        $user->setRelation('customer', $customer);
        $this->mergeGuestBags($user, $guestCart, $guestWishlist, $guestCoupon, $guestGift);
        app(\App\Services\CustomerMailService::class)->sendWelcome($user);

        $message = 'Your Geetanjali account has been created successfully.';
        $fallback = route('account.index');

        if ($request->expectsJson()) {
            $url = $request->session()->pull('url.intended', $fallback);
            $request->session()->flash('success', $message);

            return response()->json([
                'success' => true,
                'redirect' => $url,
                'message' => $message,
            ]);
        }

        return redirect()->intended($fallback)->with('success', $message);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }

    protected function redirectAfterLogin(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $fallback = $user->is_staff ? route('admin.dashboard') : route('account.index');
        $message = $user->is_staff
            ? 'Welcome back, '.$user->name
            : 'Welcome back to Geetanjali Jewellers.';

        if ($request->expectsJson()) {
            $url = $request->session()->pull('url.intended', $fallback);
            $request->session()->flash('success', $message);

            return response()->json([
                'success' => true,
                'redirect' => $url,
                'message' => $message,
            ]);
        }

        return redirect()->intended($fallback)->with('success', $message);
    }

    private function loginFailure(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => ['email' => [$message]],
            ], 422);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $message]);
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
