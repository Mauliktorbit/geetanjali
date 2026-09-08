<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\CustomerPaymentMethod;
use App\Services\AccountService;
use App\Services\CheckoutService;
use App\Support\IndianStates;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(
        private readonly AccountService $account,
        private readonly CheckoutService $checkout,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->staffRedirect($request)) {
            return $redirect;
        }

        $user = $request->user();
        $data = $this->account->dashboard($user);

        return $this->page('frontend.account.dashboard', $user, 'dashboard', [
            'dashboard' => $data,
            'breadcrumb' => $this->crumbs('My Account'),
        ]);
    }

    public function orders(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->staffRedirect($request)) {
            return $redirect;
        }

        $user = $request->user();
        $customer = $this->account->ensureCustomer($user);
        $orders = $customer->orders()->with('items')->latest()->paginate(10);

        return $this->page('frontend.account.orders', $user, 'orders', [
            'orders' => $orders,
            'breadcrumb' => $this->crumbs('My Orders', route('account.orders')),
        ]);
    }

    public function showOrder(Request $request, string $orderNumber): View|RedirectResponse
    {
        if ($redirect = $this->staffRedirect($request)) {
            return $redirect;
        }

        $user = $request->user();
        $customer = $this->account->ensureCustomer($user);
        $order = $customer->orders()->with('items')->where('order_number', $orderNumber)->firstOrFail();

        return $this->page('frontend.account.order-show', $user, 'orders', [
            'order' => $order,
            'breadcrumb' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'My Account', 'url' => route('account.index')],
                ['label' => 'My Orders', 'url' => route('account.orders')],
                ['label' => $order->order_number, 'url' => null],
            ],
        ]);
    }

    public function addresses(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->staffRedirect($request)) {
            return $redirect;
        }

        $user = $request->user();
        $customer = $this->account->ensureCustomer($user);
        $addresses = $this->checkout->addresses($customer);
        $payloads = [];
        foreach ($addresses as $address) {
            $payloads[(string) $address->id] = $this->addressFormData($address);
        }

        return $this->page('frontend.account.addresses', $user, 'addresses', [
            'addresses' => $addresses,
            'addressPayloads' => $payloads,
            'states' => IndianStates::all(),
            'breadcrumb' => $this->crumbs('Addresses', route('account.addresses')),
        ]);
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $customer = $this->account->ensureCustomer($request->user());
        $this->checkout->saveAddress($customer, $this->addressPayload($request));

        return back()->with('success', 'Address saved.');
    }

    public function updateAddress(Request $request, CustomerAddress $address): RedirectResponse
    {
        $customer = $this->account->ensureCustomer($request->user());
        $this->assertOwned($address->customer_id, $customer->id);
        $this->checkout->saveAddress($customer, $this->addressPayload($request), $address);

        return back()->with('success', 'Address updated.');
    }

    public function destroyAddress(Request $request, CustomerAddress $address): RedirectResponse
    {
        $customer = $this->account->ensureCustomer($request->user());
        $this->assertOwned($address->customer_id, $customer->id);
        $address->delete();

        return back()->with('success', 'Address removed.');
    }

    public function profile(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->staffRedirect($request)) {
            return $redirect;
        }

        $user = $request->user();

        return $this->page('frontend.account.profile', $user, 'profile', [
            'breadcrumb' => $this->crumbs('Account Details', route('account.profile')),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'mobile' => ['required', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        $mobile = preg_replace('/\D+/', '', $data['mobile']);
        $payload = [
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'mobile' => $mobile,
            'phone' => $mobile,
        ];

        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        $user->update($payload);

        $customer = $this->account->ensureCustomer($user);
        $customer->update([
            'name' => $data['name'],
            'email' => $user->email,
            'phone' => $mobile,
        ]);

        return back()->with('success', 'Account details updated.');
    }

    public function payments(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->staffRedirect($request)) {
            return $redirect;
        }

        $user = $request->user();
        $customer = $this->account->ensureCustomer($user);

        return $this->page('frontend.account.payments', $user, 'payments', [
            'paymentMethods' => $customer->paymentMethods()->orderByDesc('is_default')->orderBy('id')->get(),
            'breadcrumb' => $this->crumbs('Payment Methods', route('account.payments')),
        ]);
    }

    public function storePayment(Request $request): RedirectResponse
    {
        $customer = $this->account->ensureCustomer($request->user());
        $data = $request->validate([
            'brand' => ['required', 'in:visa,mastercard,rupay,amex'],
            'last_four' => ['required', 'digits:4'],
            'holder_name' => ['required', 'string', 'max:120'],
            'expiry_month' => ['required', 'integer', 'min:1', 'max:12'],
            'expiry_year' => ['required', 'integer', 'min:'.now()->year, 'max:'.(now()->year + 15)],
            'is_default' => ['nullable', 'boolean'],
        ]);
        $data['is_default'] = $request->boolean('is_default');
        $this->account->savePaymentMethod($customer, $data);

        return back()->with('success', 'Payment method saved.');
    }

    public function destroyPayment(Request $request, CustomerPaymentMethod $paymentMethod): RedirectResponse
    {
        $customer = $this->account->ensureCustomer($request->user());
        $this->assertOwned($paymentMethod->customer_id, $customer->id);
        $wasDefault = $paymentMethod->is_default;
        $paymentMethod->delete();

        if ($wasDefault) {
            $customer->paymentMethods()->oldest('id')->first()?->update(['is_default' => true]);
        }

        return back()->with('success', 'Payment method removed.');
    }

    public function notifications(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->staffRedirect($request)) {
            return $redirect;
        }

        $user = $request->user();
        $customer = $this->account->ensureCustomer($user);

        return $this->page('frontend.account.notifications', $user, 'notifications', [
            'prefs' => $customer->notificationPrefs(),
            'breadcrumb' => $this->crumbs('Notifications', route('account.notifications')),
        ]);
    }

    public function updateNotifications(Request $request): RedirectResponse
    {
        $customer = $this->account->ensureCustomer($request->user());
        $customer->update([
            'notification_preferences' => [
                'email' => $request->boolean('email'),
                'order_updates' => $request->boolean('order_updates'),
                'offers' => $request->boolean('offers'),
            ],
        ]);

        return back()->with('success', 'Notification preferences saved.');
    }

    public function coupons(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->staffRedirect($request)) {
            return $redirect;
        }

        $user = $request->user();

        return $this->page('frontend.account.coupons', $user, 'coupons', [
            'coupons' => $this->account->activeCoupons(),
            'breadcrumb' => $this->crumbs('My Coupons', route('account.coupons')),
        ]);
    }

    public function returns(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->staffRedirect($request)) {
            return $redirect;
        }

        $user = $request->user();
        $customer = $this->account->ensureCustomer($user);
        $returns = $customer->returnRequests()->with('order')->latest()->get();

        return $this->page('frontend.account.returns', $user, 'returns', [
            'returns' => $returns,
            'breadcrumb' => $this->crumbs('Returns & Refunds', route('account.returns')),
        ]);
    }

    public function rewards(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->staffRedirect($request)) {
            return $redirect;
        }

        $user = $request->user();
        $customer = $this->account->ensureCustomer($user);

        return $this->page('frontend.account.rewards', $user, 'rewards', [
            'transactions' => $customer->rewardPointTransactions()->latest()->limit(20)->get(),
            'breadcrumb' => $this->crumbs('Reward Points', route('account.rewards')),
        ]);
    }

    public function help(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->staffRedirect($request)) {
            return $redirect;
        }

        $user = $request->user();

        return $this->page('frontend.account.help', $user, 'help', [
            'breadcrumb' => $this->crumbs('Help & Support', route('account.help')),
        ]);
    }

    private function staffRedirect(Request $request): ?RedirectResponse
    {
        if ($request->user()?->is_staff) {
            return redirect()->route('admin.dashboard');
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function page(string $view, $user, string $section, array $data = []): View
    {
        $customer = $this->account->ensureCustomer($user);

        return view($view, array_merge([
            'user' => $user,
            'customer' => $customer,
            'accountSection' => $section,
        ], $data));
    }

    /**
     * @return list<array{label: string, url: string|null}>
     */
    private function crumbs(string $label, ?string $url = null): array
    {
        $items = [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'My Account', 'url' => $url ? route('account.index') : null],
        ];

        if ($url) {
            $items[] = ['label' => $label, 'url' => null];
        }

        return $items;
    }

    private function assertOwned(int $ownerId, int $customerId): void
    {
        if ($ownerId !== $customerId) {
            abort(403);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function addressPayload(Request $request): array
    {
        $data = $request->validate([
            'label' => ['required', 'in:home,office,other'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'address_line1' => ['required', 'string', 'max:180'],
            'address_line2' => ['nullable', 'string', 'max:180'],
            'city' => ['required', 'string', 'max:80'],
            'state' => ['required', 'string', 'max:80'],
            'pincode' => ['required', 'string', 'regex:/^\d{6}$/'],
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
}
