<?php

namespace App\Providers;

use App\Models\User;
use App\Services\CartService;
use App\Services\Integrations\Contracts\CourierInterface;
use App\Services\Integrations\Contracts\EmailProviderInterface;
use App\Services\Integrations\Contracts\PaymentGatewayInterface;
use App\Services\Integrations\Contracts\SmsGatewayInterface;
use App\Services\Integrations\Contracts\WhatsAppGatewayInterface;
use App\Services\Integrations\NullCourierAdapter;
use App\Services\Integrations\NullPaymentGateway;
use App\Services\WishlistService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, NullPaymentGateway::class);
        $this->app->bind(CourierInterface::class, NullCourierAdapter::class);
        $this->app->bind(SmsGatewayInterface::class, function () {
            return new class implements SmsGatewayInterface
            {
                public function send(string $to, string $message, array $options = []): array
                {
                    return ['success' => true, 'provider' => 'null'];
                }
            };
        });
        $this->app->bind(WhatsAppGatewayInterface::class, function () {
            return new class implements WhatsAppGatewayInterface
            {
                public function sendText(string $to, string $message, array $options = []): array
                {
                    return ['success' => true, 'provider' => 'null'];
                }

                public function sendTemplate(string $to, string $template, array $params = [], array $options = []): array
                {
                    return ['success' => true, 'provider' => 'null'];
                }
            };
        });
        $this->app->bind(EmailProviderInterface::class, function () {
            return new class implements EmailProviderInterface
            {
                public function send(string $to, string $subject, string $html, array $options = []): array
                {
                    return ['success' => true, 'provider' => 'null'];
                }
            };
        });
    }

    public function boot(): void
    {
        Auth::guard('web')->setRememberDuration(525600);

        View::share('brandName', config('brand.name'));
        View::share('brandTagline', config('brand.tagline'));

        View::composer(['frontend.components.navbar', 'frontend.components.footer'], function ($view) {
            $view->with('storefrontCollections', \App\Services\StorefrontCatalogService::navCollections());
        });

        View::composer('frontend.components.navbar', function ($view) {
            $view->with('cartCount', app(CartService::class)->count());
            $view->with('wishlistCount', app(WishlistService::class)->count());
            $view->with('navItems', \App\Services\StorefrontCatalogService::navMenuItems());
        });

        View::composer('frontend.layouts.app', function ($view) {
            $prompt = null;
            $user = Auth::user();

            if ($user && ! $user->is_staff && $user->customer) {
                $skip = request()->routeIs(
                    'checkout.index',
                    'account.reviews',
                    'login',
                    'register',
                    'password.request',
                    'password.email',
                    'password.reset',
                    'password.update',
                    'password.otp',
                    'password.otp.verify',
                    'password.otp.resend',
                );

                if (! $skip) {
                    $prompt = app(\App\Services\ReviewService::class)->pendingPrompt(
                        $user->customer,
                        (array) session('review_prompt_later', [])
                    );
                }
            }

            if (is_array($prompt)) {
                $prompt['store_url'] = route('account.reviews.store');
                $prompt['later_url'] = route('account.reviews.later');
            }

            $view->with('deliveryReviewPrompt', $prompt);
        });

        View::composer('admin.layouts.app', function ($view) {
            $user = Auth::user();
            if (! $user || ! $user->is_staff) {
                $view->with([
                    'adminNotifyCount' => 0,
                    'adminNotifications' => collect(),
                    'adminNotifyAlerts' => collect(),
                ]);

                return;
            }

            $notifications = app(\App\Services\NotificationService::class);
            $view->with([
                'adminNotifyCount' => $notifications->unreadCount($user->id),
                'adminNotifications' => $notifications->latest($user->id, 8),
                'adminNotifyAlerts' => $notifications->unreadAlerts($user->id),
            ]);
        });

        Route::bind('staff', function (string $value) {
            return User::where('is_staff', true)->findOrFail($value);
        });
    }
}
