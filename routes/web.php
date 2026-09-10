<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Frontend\AboutController;
use App\Http\Controllers\Frontend\AccountController;
use App\Http\Controllers\Frontend\BridalCollectionController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\CollectionController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\NewArrivalsController;
use App\Http\Controllers\Frontend\OfferController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about-us', [AboutController::class, 'index'])->name('about');
Route::get('/contact-us', [ContactController::class, 'index'])->name('contact');
Route::post('/contact-us', [ContactController::class, 'store'])->name('contact.store');

Route::get('/faq', fn () => app(PageController::class)->show('faq'))->name('pages.faq');
Route::get('/shipping-policy', fn () => app(PageController::class)->show('shipping-policy'))->name('pages.shipping');
Route::get('/returns', fn () => app(PageController::class)->show('returns'))->name('pages.returns');
Route::get('/terms-and-conditions', fn () => app(PageController::class)->show('terms'))->name('pages.terms');
Route::get('/privacy-policy', fn () => app(PageController::class)->show('privacy'))->name('pages.privacy');
Route::get('/help', fn () => app(PageController::class)->show('help'))->name('pages.help');
Route::get('/store-locator', fn () => app(PageController::class)->show('store-locator'))->name('pages.store-locator');
Route::get('/compare', fn () => app(PageController::class)->show('compare'))->name('pages.compare');
Route::get('/track-order', [PageController::class, 'trackOrder'])->name('pages.track-order');
Route::post('/track-order', [PageController::class, 'trackOrder'])->name('pages.track-order.submit');
Route::post('/newsletter', [PageController::class, 'newsletter'])->name('newsletter.subscribe');

Route::get('/kundan', [CollectionController::class, 'kundan'])->name('collections.kundan');
Route::get('/bridal-collection', [BridalCollectionController::class, 'index'])->name('collections.bridal');
Route::get('/new-arrivals', [NewArrivalsController::class, 'index'])->name('products.new-arrivals');
Route::get('/collection/{slug}', [CollectionController::class, 'show'])->name('collections.show');
Route::get('/offers', [OfferController::class, 'index'])->name('offers.index');

Route::get('/product/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::post('/delivery/check', [ProductController::class, 'checkDelivery'])->name('delivery.check');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');
Route::post('/cart/gift-message', [CartController::class, 'saveGiftMessage'])->name('cart.gift');

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place');
    Route::post('/checkout/contact', [CheckoutController::class, 'updateContact'])->name('checkout.contact');
    Route::post('/checkout/address', [CheckoutController::class, 'storeAddress'])->name('checkout.address.store');
    Route::put('/checkout/address/{address}', [CheckoutController::class, 'updateAddress'])->name('checkout.address.update');
    Route::get('/order-confirmation/{orderNumber}', [CheckoutController::class, 'success'])->name('checkout.success');

    Route::get('/my-account', [AccountController::class, 'index'])->name('account.index');
    Route::get('/my-account/orders', [AccountController::class, 'orders'])->name('account.orders');
    Route::get('/my-account/orders/{orderNumber}', [AccountController::class, 'showOrder'])->name('account.orders.show');
    Route::post('/my-account/orders/{orderNumber}/cancel', [AccountController::class, 'cancelOrder'])->name('account.orders.cancel');
    Route::post('/my-account/orders/{orderNumber}/return', [AccountController::class, 'storeReturn'])->name('account.returns.store');
    Route::get('/my-account/addresses', [AccountController::class, 'addresses'])->name('account.addresses');
    Route::post('/my-account/addresses', [AccountController::class, 'storeAddress'])->name('account.addresses.store');
    Route::put('/my-account/addresses/{address}', [AccountController::class, 'updateAddress'])->name('account.addresses.update');
    Route::delete('/my-account/addresses/{address}', [AccountController::class, 'destroyAddress'])->name('account.addresses.destroy');
    Route::get('/my-account/details', [AccountController::class, 'profile'])->name('account.profile');
    Route::put('/my-account/details', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::get('/my-account/payments', [AccountController::class, 'payments'])->name('account.payments');
    Route::post('/my-account/payments', [AccountController::class, 'storePayment'])->name('account.payments.store');
    Route::delete('/my-account/payments/{paymentMethod}', [AccountController::class, 'destroyPayment'])->name('account.payments.destroy');
    Route::get('/my-account/notifications', [AccountController::class, 'notifications'])->name('account.notifications');
    Route::put('/my-account/notifications', [AccountController::class, 'updateNotifications'])->name('account.notifications.update');
    Route::get('/my-account/coupons', [AccountController::class, 'coupons'])->name('account.coupons');
    Route::get('/my-account/returns', [AccountController::class, 'returns'])->name('account.returns');
    Route::get('/my-account/returns/{returnNumber}', [AccountController::class, 'showReturn'])->name('account.returns.show');
    Route::get('/my-account/rewards', [AccountController::class, 'rewards'])->name('account.rewards');
    Route::get('/my-account/help', [AccountController::class, 'help'])->name('account.help');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/add', [WishlistController::class, 'add'])->name('wishlist.add');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::delete('/wishlist/remove/{id}', [WishlistController::class, 'remove'])->name('wishlist.remove');
Route::post('/wishlist/move/{id}', [WishlistController::class, 'moveToBag'])->name('wishlist.move');
Route::post('/wishlist/move-all', [WishlistController::class, 'moveAllToBag'])->name('wishlist.move-all');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
    Route::get('/forgot-password', [PasswordResetController::class, 'showEmailForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendOtp'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/forgot-password/otp', [PasswordResetController::class, 'showOtpForm'])->name('password.otp');
    Route::post('/forgot-password/otp', [PasswordResetController::class, 'verifyOtp'])->middleware('throttle:10,1')->name('password.otp.verify');
    Route::post('/forgot-password/otp/resend', [PasswordResetController::class, 'resendOtp'])->middleware('throttle:3,1')->name('password.otp.resend');
    Route::get('/forgot-password/reset', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/forgot-password/reset', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
});
