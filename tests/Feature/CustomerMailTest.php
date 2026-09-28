<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Mail\OrderConfirmedMail;
use App\Mail\OrderShippedMail;
use App\Mail\OrderStatusMail;
use App\Mail\WelcomeCustomerMail;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\CustomerMailService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CustomerMailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    public function test_registration_sends_a_welcome_email(): void
    {
        Mail::fake();

        $this->postJson('/register', [
            'name' => 'Riya Sharma',
            'email' => 'riya@example.com',
            'mobile' => '9876543210',
            'password' => 'password12',
            'password_confirmation' => 'password12',
            'terms' => '1',
        ])->assertOk();

        Mail::assertSent(WelcomeCustomerMail::class, function (WelcomeCustomerMail $mail) {
            return $mail->hasTo('riya@example.com');
        });
    }

    public function test_order_confirmation_email_includes_totals_and_tracking_link(): void
    {
        Mail::fake();

        $order = $this->makeCustomerOrder();
        app(CustomerMailService::class)->sendOrderConfirmed($order);

        Mail::assertSent(OrderConfirmedMail::class, function (OrderConfirmedMail $mail) use ($order) {
            return $mail->hasTo('riya@example.com')
                && $mail->order->is($order)
                && str_contains($mail->trackUrl, $order->order_number);
        });
    }

    public function test_shipping_an_order_sends_a_tracking_email(): void
    {
        Mail::fake();

        $order = $this->makeCustomerOrder();
        $order->update(['tracking_number' => 'TRK123456']);

        app(OrderService::class)->updateStatus($order, OrderStatus::SHIPPED);

        Mail::assertSent(OrderShippedMail::class, function (OrderShippedMail $mail) {
            return $mail->hasTo('riya@example.com') && $mail->trackingNumber === 'TRK123456';
        });
    }

    public function test_cancelling_an_order_emails_the_customer(): void
    {
        Mail::fake();

        $order = $this->makeCustomerOrder();
        app(OrderService::class)->cancelOrder($order, 'Customer requested');

        Mail::assertSent(OrderStatusMail::class, function (OrderStatusMail $mail) {
            return $mail->hasTo('riya@example.com') && $mail->kind === 'cancelled';
        });
    }

    private function makeCustomerOrder(): Order
    {
        $user = User::factory()->create([
            'name' => 'Riya Sharma',
            'email' => 'riya@example.com',
            'last_login_at' => now(),
        ]);
        $customer = Customer::forUser($user);

        $order = Order::query()->create([
            'order_number' => 'GJMAIL1001',
            'customer_id' => $customer->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'status' => OrderStatus::CONFIRMED,
            'payment_status' => 'paid',
            'payment_method' => 'upi',
            'shipping_method' => 'standard',
            'coupon_code' => 'TEST30',
            'subtotal' => 38500,
            'discount_amount' => 11550,
            'shipping_charge' => 0,
            'grand_total' => 26950,
            'currency' => 'INR',
            'confirmed_at' => now(),
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_name' => 'Kundan Necklace',
            'quantity' => 1,
            'unit_price' => 38500,
            'total' => 38500,
        ]);

        return $order->load('items');
    }
}
