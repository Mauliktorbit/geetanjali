<?php

namespace App\Http\Controllers\Admin;

use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends AdminController
{
    public function __construct(protected SettingService $settingService) {}

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'general');

        return view('admin.settings.index', [
            'tab' => $tab,
            'general' => $this->settingService->getGroup('general'),
            'order' => $this->settingService->getGroup('order'),
            'product' => $this->settingService->getGroup('product'),
            'customer' => $this->settingService->getGroup('customer'),
            'invoice' => $this->settingService->getGroup('invoice'),
        ]);
    }

    public function updateGeneral(Request $request)
    {
        $data = $request->validate([
            'store_name' => ['nullable', 'string', 'max:255'],
            'store_email' => ['nullable', 'email'],
            'store_phone' => ['nullable', 'string', 'max:30'],
            'store_address' => ['nullable', 'string'],
            'store_city' => ['nullable', 'string', 'max:100'],
            'store_state' => ['nullable', 'string', 'max:100'],
            'store_country' => ['nullable', 'string', 'max:100'],
            'store_pincode' => ['nullable', 'string', 'max:20'],
            'store_gstin' => ['nullable', 'string', 'max:20'],
            'currency' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:50'],
        ]);

        $this->settingService->setMany($data, 'general');

        return $this->success('General settings saved.', 'admin.settings.index', ['tab' => 'general']);
    }

    public function updateOrder(Request $request)
    {
        $data = $request->validate([
            'order_prefix' => ['nullable', 'string', 'max:20'],
            'auto_confirm_orders' => ['nullable', 'boolean'],
            'allow_guest_checkout' => ['nullable', 'boolean'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'cod_enabled' => ['nullable', 'boolean'],
            'cod_max_amount' => ['nullable', 'numeric', 'min:0'],
            'default_warehouse_id' => ['nullable', 'integer'],
        ]);

        $data['auto_confirm_orders'] = $request->boolean('auto_confirm_orders');
        $data['allow_guest_checkout'] = $request->boolean('allow_guest_checkout');
        $data['cod_enabled'] = $request->boolean('cod_enabled');

        $this->settingService->setMany($data, 'order');

        return $this->success('Order settings saved.', 'admin.settings.index', ['tab' => 'order']);
    }

    public function updateProduct(Request $request)
    {
        $data = $request->validate([
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'allow_backorders' => ['nullable', 'boolean'],
            'show_out_of_stock' => ['nullable', 'boolean'],
            'default_tax_rate_id' => ['nullable', 'integer'],
            'weight_unit' => ['nullable', 'string', 'max:10'],
            'dimension_unit' => ['nullable', 'string', 'max:10'],
        ]);

        $data['allow_backorders'] = $request->boolean('allow_backorders');
        $data['show_out_of_stock'] = $request->boolean('show_out_of_stock');

        $this->settingService->setMany($data, 'product');

        return $this->success('Product settings saved.', 'admin.settings.index', ['tab' => 'product']);
    }

    public function updateCustomer(Request $request)
    {
        $data = $request->validate([
            'default_customer_group_id' => ['nullable', 'integer'],
            'reward_points_enabled' => ['nullable', 'boolean'],
            'points_per_currency' => ['nullable', 'numeric', 'min:0'],
            'wallet_enabled' => ['nullable', 'boolean'],
            'require_email_verification' => ['nullable', 'boolean'],
        ]);

        $data['reward_points_enabled'] = $request->boolean('reward_points_enabled');
        $data['wallet_enabled'] = $request->boolean('wallet_enabled');
        $data['require_email_verification'] = $request->boolean('require_email_verification');

        $this->settingService->setMany($data, 'customer');

        return $this->success('Customer settings saved.', 'admin.settings.index', ['tab' => 'customer']);
    }

    public function updateInvoice(Request $request)
    {
        $data = $request->validate([
            'invoice_prefix' => ['nullable', 'string', 'max:20'],
            'invoice_footer' => ['nullable', 'string'],
            'invoice_terms' => ['nullable', 'string'],
            'show_hsn' => ['nullable', 'boolean'],
            'show_bank_details' => ['nullable', 'boolean'],
            'bank_details' => ['nullable', 'string'],
        ]);

        $data['show_hsn'] = $request->boolean('show_hsn');
        $data['show_bank_details'] = $request->boolean('show_bank_details');

        $this->settingService->setMany($data, 'invoice');

        return $this->success('Invoice settings saved.', 'admin.settings.index', ['tab' => 'invoice']);
    }
}
