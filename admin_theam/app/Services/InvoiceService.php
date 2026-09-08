<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\TaxRate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class InvoiceService
{
    public function __construct(protected SettingService $settingService) {}

    public function generateTaxInvoice(Order $order, ?string $placeOfSupply = null): Invoice
    {
        return DB::transaction(function () use ($order, $placeOfSupply) {
            $order->loadMissing(['items.product.taxRate', 'customer']);

            if ($order->items->isEmpty()) {
                throw new InvalidArgumentException('Cannot invoice an order with no items.');
            }

            $storeState = (string) $this->settingService->get('store_state', 'Maharashtra');
            $placeOfSupply = $placeOfSupply
                ?? $order->shipping_state
                ?? ($order->shipping_address['state'] ?? $storeState);

            $isIntraState = $this->normalizeState($placeOfSupply) === $this->normalizeState($storeState);

            $subtotal = 0.0;
            $cgst = 0.0;
            $sgst = 0.0;
            $igst = 0.0;
            $breakup = [];

            foreach ($order->items as $item) {
                $line = ((float) $item->unit_price * (int) $item->quantity) - (float) $item->discount;
                $subtotal += $line;

                $taxRate = $item->product?->taxRate;
                $rateCgst = (float) ($taxRate?->cgst ?? 0);
                $rateSgst = (float) ($taxRate?->sgst ?? 0);
                $rateIgst = (float) ($taxRate?->igst ?? ((float) $item->tax_rate ?: ($rateCgst + $rateSgst)));

                if ($isIntraState) {
                    if ($rateCgst === 0.0 && $rateSgst === 0.0 && $rateIgst > 0) {
                        $rateCgst = $rateIgst / 2;
                        $rateSgst = $rateIgst / 2;
                    }
                    $lineCgst = round($line * ($rateCgst / 100), 2);
                    $lineSgst = round($line * ($rateSgst / 100), 2);
                    $lineIgst = 0.0;
                } else {
                    if ($rateIgst === 0.0) {
                        $rateIgst = $rateCgst + $rateSgst;
                    }
                    $lineCgst = 0.0;
                    $lineSgst = 0.0;
                    $lineIgst = round($line * ($rateIgst / 100), 2);
                }

                $cgst += $lineCgst;
                $sgst += $lineSgst;
                $igst += $lineIgst;

                $hsn = $item->hsn_sac ?? $taxRate?->hsn_sac ?? 'N/A';
                if (! isset($breakup[$hsn])) {
                    $breakup[$hsn] = [
                        'hsn_sac' => $hsn,
                        'taxable' => 0,
                        'cgst' => 0,
                        'sgst' => 0,
                        'igst' => 0,
                    ];
                }
                $breakup[$hsn]['taxable'] += $line;
                $breakup[$hsn]['cgst'] += $lineCgst;
                $breakup[$hsn]['sgst'] += $lineSgst;
                $breakup[$hsn]['igst'] += $lineIgst;
            }

            $taxAmount = $cgst + $sgst + $igst;
            $grand = $subtotal - (float) $order->discount_amount + $taxAmount
                + (float) $order->shipping_charge + (float) $order->cod_charge;

            $invoice = Invoice::create([
                'order_id' => $order->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'type' => 'tax_invoice',
                'invoice_date' => now()->toDateString(),
                'financial_year' => $this->financialYear(),
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round($taxAmount, 2),
                'cgst' => round($cgst, 2),
                'sgst' => round($sgst, 2),
                'igst' => round($igst, 2),
                'grand_total' => round($grand, 2),
                'place_of_supply' => $placeOfSupply,
                'is_b2b' => filled($order->customer?->gstin),
                'customer_gstin' => $order->customer?->gstin,
                'tax_breakup' => array_values($breakup),
            ]);

            $pdfPath = $this->generatePdf($invoice->fresh(['order.items', 'order.customer']));
            $invoice->update(['pdf_path' => $pdfPath]);

            return $invoice->fresh();
        });
    }

    public function generatePdf(Invoice $invoice): string
    {
        $invoice->loadMissing(['order.items', 'order.customer']);

        $html = view('pdf.tax-invoice', [
            'invoice' => $invoice,
            'order' => $invoice->order,
            'store' => [
                'name' => $this->settingService->get('store_name', config('app.name')),
                'address' => $this->settingService->get('store_address', ''),
                'gstin' => $this->settingService->get('store_gstin', ''),
                'state' => $this->settingService->get('store_state', ''),
            ],
        ])->render();

        $pdf = Pdf::loadHTML($html)->setPaper('a4');
        $path = 'invoices/' . $invoice->invoice_number . '.pdf';
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    protected function generateInvoiceNumber(): string
    {
        $fy = $this->financialYear();
        $prefix = 'INV/' . $fy . '/';

        $last = Invoice::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('invoice_number');

        $seq = 1;
        if ($last && preg_match('/(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix . str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }

    protected function financialYear(): string
    {
        $now = now();
        $start = $now->month >= 4 ? $now->year : $now->year - 1;
        $end = $start + 1;

        return substr((string) $start, -2) . '-' . substr((string) $end, -2);
    }

    protected function normalizeState(string $state): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $state)));
    }
}
