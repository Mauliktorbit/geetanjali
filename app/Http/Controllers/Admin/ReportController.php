<?php

namespace App\Http\Controllers\Admin;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends AdminController
{
    public function __construct(protected ReportService $reportService) {}

    public function index()
    {
        return view('admin.reports.index');
    }

    public function sales(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'group_by']);
        $rows = $this->reportService->salesReport($filters);

        if ($request->get('export') === 'csv') {
            return $this->exportCsv('sales-report', ['Period', 'Orders', 'Subtotal', 'Discount', 'Tax', 'Shipping', 'Revenue', 'Cost', 'Profit'], $rows->map(fn ($r) => [
                $r->period, $r->orders, $r->subtotal, $r->discount, $r->tax, $r->shipping, $r->revenue, $r->cost, $r->profit,
            ]));
        }

        return view('admin.reports.sales', compact('rows', 'filters'));
    }

    public function products(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'product_id']);
        $rows = $this->reportService->productReport($filters);

        if ($request->get('export') === 'csv') {
            return $this->exportCsv('product-report', ['Product', 'SKU', 'Units', 'Orders', 'Revenue', 'Cost', 'Profit'], $rows->map(fn ($r) => [
                $r->product_name, $r->sku, $r->units_sold, $r->orders, $r->revenue, $r->cost, $r->profit,
            ]));
        }

        return view('admin.reports.products', compact('rows', 'filters'));
    }

    public function customers(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'customer_group_id', 'limit']);
        $rows = $this->reportService->customerReport($filters);

        if ($request->get('export') === 'csv') {
            return $this->exportCsv('customer-report', ['Name', 'Email', 'Phone', 'Total Orders', 'Total Spent', 'Period Orders', 'Period Spent'], $rows->map(fn ($r) => [
                $r->name, $r->email, $r->phone, $r->total_orders, $r->total_spent, $r->period_orders, $r->period_spent,
            ]));
        }

        return view('admin.reports.customers', compact('rows', 'filters'));
    }

    public function profitability(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to']);
        $rows = $this->reportService->profitabilityReport($filters);

        if ($request->get('export') === 'csv') {
            return $this->exportCsv('profitability-report', ['Metric', 'Amount'], $rows->map(fn ($r) => [
                $r['metric'], $r['amount'],
            ]));
        }

        return view('admin.reports.profitability', compact('rows', 'filters'));
    }

    protected function exportCsv(string $name, array $headers, $rows): StreamedResponse
    {
        $filename = $name . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, is_array($row) ? $row : (array) $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
