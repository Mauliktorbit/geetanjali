<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $summary = $this->cart->summary();

        if ($summary['count'] < 1) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $customer = $this->checkout->ensureCustomer($user);
        $addresses = $this->checkout->addresses($customer);
        $selectedId = collect($addresses)->first(fn ($address) => $address->is_default)?->id
            ?? collect($addresses)->first()?->id;
        $addressPayloads = [];
        foreach ($addresses as $address) {
            $addressPayloads[(string) $address->id] = $this->addressFormData($address);
        }

        return view('frontend.checkout.index', [
            'user' => $user,
            'customer' => $customer,
            'addresses' => $addresses,
            'addressPayloads' => $addressPayloads,
            'selectedAddressId' => $selectedId,
            'cart' => $summary,
            'shippingOptions' => CheckoutService::SHIPPING,
            'paymentOptions' => CheckoutService::PAYMENTS,
            'states' => $this->indianStates(),
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Cart', 'url' => route('cart.index')],
                ['label' => 'Checkout', 'url' => null],
            ],
        ]);
    }

    public function updateContact(Request $request): RedirectResponse
    {
        $user = $request->user();
        $request->merge([
            'mobile' => indian_mobile($request->input('mobile')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'mobile' => indian_mobile_rules(true),
        ], [
            'mobile.required' => 'Please enter your mobile number.',
            'mobile.regex' => 'Enter a valid 10-digit mobile number.',
        ]);

        $mobile = $data['mobile'];

        $user->update([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'mobile' => $mobile,
            'phone' => $mobile,
        ]);

        $customer = $this->checkout->ensureCustomer($user);
        $customer->update([
            'name' => $data['name'],
            'email' => $user->email,
            'phone' => $mobile,
        ]);

        return back()->with('success', 'Contact information updated.');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $customer = $this->checkout->ensureCustomer($request->user());
        $this->checkout->saveAddress($customer, $this->addressPayload($request));

        return back()->with('success', 'Address saved.');
    }

    public function updateAddress(Request $request, CustomerAddress $address): RedirectResponse
    {
        $customer = $this->checkout->ensureCustomer($request->user());

        if ((int) $address->customer_id !== (int) $customer->id) {
            abort(403);
        }

        $this->checkout->saveAddress($customer, $this->addressPayload($request), $address);

        return back()->with('success', 'Address updated.');
    }

    public function place(Request $request): RedirectResponse
    {
        $user = $request->user();
        $summary = $this->cart->summary();

        if ($summary['count'] < 1) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $data = $request->validate([
            'address_id' => ['required', 'integer'],
            'shipping_method' => ['required', 'in:standard,express'],
            'payment_method' => ['required', 'in:upi,card,netbanking,cod,wallet'],
        ], [
            'address_id.required' => 'Please select a delivery address.',
        ]);

        try {
            $customer = $this->checkout->ensureCustomer($user);
            $order = $this->checkout->placeOrder($user, $customer, $data);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('checkout.success', $order->order_number)
            ->with('success', 'Your order has been placed successfully.');
    }

    public function success(Request $request, string $orderNumber): View|RedirectResponse
    {
        $user = $request->user();
        $customer = $this->checkout->ensureCustomer($user);

        $order = Order::query()
            ->with('items')
            ->where('order_number', $orderNumber)
            ->where('customer_id', $customer->id)
            ->first();

        if (! $order) {
            return redirect()->route('account.index')->with('error', 'Order not found.');
        }

        return view('frontend.checkout.success', [
            'order' => $order,
            'expectedDelivery' => $this->checkout->expectedDelivery($order),
            'shippingLabel' => CheckoutService::SHIPPING[$order->shipping_method]['label'] ?? 'Standard Delivery',
            'shippingEta' => CheckoutService::SHIPPING[$order->shipping_method]['eta'] ?? '3–5 business days',
            'paymentLabel' => CheckoutService::PAYMENTS[$order->payment_method] ?? ucfirst((string) $order->payment_method),
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Checkout', 'url' => route('checkout.index')],
                ['label' => 'Order confirmed', 'url' => null],
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function addressPayload(Request $request): array
    {
        $pincode = digits_only($request->input('pincode'));
        $request->merge([
            'phone' => indian_mobile($request->input('phone')),
            'pincode' => $pincode === '' ? null : $pincode,
        ]);

        $data = $request->validate([
            'label' => ['required', 'in:home,office,other'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => indian_mobile_rules(true),
            'address_line1' => ['required', 'string', 'max:180'],
            'address_line2' => ['nullable', 'string', 'max:180'],
            'city' => ['required', 'string', 'max:80'],
            'state' => ['required', 'string', 'max:80'],
            'pincode' => indian_pincode_rules(true),
        ], [
            'phone.required' => 'Please enter a 10-digit mobile number.',
            'phone.regex' => 'Enter a valid 10-digit mobile number.',
            'pincode.regex' => 'Enter a valid 6-digit pincode.',
        ]);

        $data['is_default'] = $request->boolean('is_default');

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function addressFormData(CustomerAddress $address): array
    {
        return [
            'id' => $address->id,
            'label' => $address->label ?: 'home',
            'name' => $address->name,
            'phone' => $address->phone,
            'address_line1' => $address->address_line1,
            'address_line2' => $address->address_line2,
            'city' => $address->city,
            'state' => $address->state,
            'pincode' => $address->pincode,
            'is_default' => (bool) $address->is_default,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function indianStates(): array
    {
        return [
            'Andhra Pradesh' => 'Andhra Pradesh',
            'Delhi' => 'Delhi',
            'Goa' => 'Goa',
            'Gujarat' => 'Gujarat',
            'Haryana' => 'Haryana',
            'Karnataka' => 'Karnataka',
            'Kerala' => 'Kerala',
            'Madhya Pradesh' => 'Madhya Pradesh',
            'Maharashtra' => 'Maharashtra',
            'Punjab' => 'Punjab',
            'Rajasthan' => 'Rajasthan',
            'Tamil Nadu' => 'Tamil Nadu',
            'Telangana' => 'Telangana',
            'Uttar Pradesh' => 'Uttar Pradesh',
            'West Bengal' => 'West Bengal',
        ];
    }
}
