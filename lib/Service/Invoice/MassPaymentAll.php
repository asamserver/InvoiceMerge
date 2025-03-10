<?php

namespace WHMCS\Module\Addon\InvoicePaid\Service\Invoice;

use WHMCS\Module\Addon\InvoicePaid\Models\Invoice;
use WHMCS\Module\Addon\InvoicePaid\Models\InvoiceItem;
use WHMCS\Module\Addon\InvoicePaid\Service\BaseService;
use Carbon\Carbon;

class MassPaymentAll extends BaseService
{
    public static function handle($request): array
    {
        if (!isset($request['userid'])) {
            return self::errorResponse('userid is required');
        }

        $invoices = Invoice::where('userid', $request['userid'])
            ->where('status', 'Unpaid')
            ->whereHas('items', function ($q) {
                $q->where('type', '!=', 'Invoice');
            })->pluck('id')->toArray();

        if (count($invoices) < 2) {
            return self::errorResponse('Can\'t create mass pay invoice with less than 2 invoices');
        }


        $emptyInvoice = localAPI('CreateInvoice', ['userid' => $request['userid'], 'noemails' => true]);


        if (!$emptyInvoice['invoiceid']) {
            return self::errorResponse('Something went wrong');
        }

        $invoiceID = $emptyInvoice['invoiceid'];

        $invoiceItemsData = [];
        foreach ($invoices as $invoice) {
            $invoiceItemsData[] = [
                'userid' => $request['userid'],
                'type' => 'Invoice',
                'relid' => $invoice->id,
                'invoiceid' => $invoiceID,
                'description' => 'Invoice #' . $invoice->id,
                'amount' => $invoice->total,
                'taxed' => 0,
                'duedate' => Carbon::now()->format('Y-m-d'),
                'paymentmethod' => 'fastspring',
                'notes' => ''
            ];
        }
        InvoiceItem::insert($invoiceItemsData);

        localAPI('UpdateInvoice', ['invoiceid' => $invoiceID, 'duedate' => Carbon::now()->format('Y-m-d')]);

        return self::successResponse(['invoiceid' => $invoiceID]);
    }
}
