<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
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

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
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

        $guestCart = $request->session()->get('cart');
        $guestWishlist = $request->session()->get('wishlist');
        $guestCoupon = $request->session()->get('cart_coupon');
        $guestGift = $request->session()->get('cart_gift_message');

        $request->session()->regenerate();

        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $this->mergeGuestBags($user, $guestCart, $guestWishlist, $guestCoupon, $guestGift);

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

        Auth::login($user);
        $request->session()->regenerate();

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
}
