<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Integrations\Contracts\CourierInterface;
use App\Services\Integrations\Contracts\EmailProviderInterface;
use App\Services\Integrations\Contracts\PaymentGatewayInterface;
use App\Services\Integrations\Contracts\SmsGatewayInterface;
use App\Services\Integrations\Contracts\WhatsAppGatewayInterface;
use App\Services\Integrations\NullCourierAdapter;
use App\Services\Integrations\NullPaymentGateway;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, NullPaymentGateway::class);
        $this->app->bind(CourierInterface::class, NullCourierAdapter::class);
        $this->app->bind(SmsGatewayInterface::class, function () {
            return new class implements SmsGatewayInterface {
                public function send(string $to, string $message, array $options = []): array
                {
                    return ['success' => true, 'provider' => 'null'];
                }
            };
        });
        $this->app->bind(WhatsAppGatewayInterface::class, function () {
            return new class implements WhatsAppGatewayInterface {
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
            return new class implements EmailProviderInterface {
                public function send(string $to, string $subject, string $html, array $options = []): array
                {
                    return ['success' => true, 'provider' => 'null'];
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('staff', function (string $value) {
            return User::where('is_staff', true)->findOrFail($value);
        });
    }
}
