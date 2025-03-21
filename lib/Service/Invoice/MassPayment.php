<?php

namespace WHMCS\Module\Addon\InvoiceMerge\Service\Invoice;

use WHMCS\Module\Addon\InvoiceMerge\Models\Invoice;
use WHMCS\Module\Addon\InvoiceMerge\Models\InvoiceItem;
use WHMCS\Module\Addon\InvoiceMerge\Service\BaseService;
use Carbon\Carbon;

class MassPayment extends BaseService
{
    public static function handle($request): array
    {
        if (!isset($request['userid'])) {
            return self::errorResponse('userid is required');
        }
        $invoices = collect($request['invoices'])->map(function ($id) {
            return Invoice::find((int) $id);
        })->filter(function ($invoice) {
            return $invoice && $invoice->status === 'Unpaid'; // Only unpaid invoices
        })->values();

        if ($invoices->count() < 2) {
            return self::errorResponse('Can\'t create mass pay invoice with less than 2 unpaid invoices');
        }
        $emptyInvoice = localAPI('CreateInvoice', [
            'userid' => $request['userid'],
            'duedate' => Carbon::now()->format('Y-m-d'),
            'date' => Carbon::now()->format('Y-m-d'),
            'noemails' => true
        ]);

        if (!$emptyInvoice['invoiceid']) {
            return self::errorResponse('Something went wrong');
        }

        $total = 0;
        foreach ($invoices as $invoice) {
            $total += $invoice->total;
            InvoiceItem::create([
                'userid' => $request['userid'],
                'type' => 'Invoice',
                'relid' => (int) $invoice->id,
                'invoiceid' => $emptyInvoice['invoiceid'],
                'description' => 'Invoice #' . $invoice->id,
                'amount' => $invoice->total,
                'taxed' => 0,
                'duedate' => Carbon::now()->format('Y-m-d'),
                'paymentmethod' => 'fastspring',
                'notes' => ''
            ]);
        }
        $invoice = Invoice::find($emptyInvoice['invoiceid']);
        $invoice->total = $total;
        $invoice->save();
        return self::successResponse(['invoiceid' => $invoice->id]);
    }
}
