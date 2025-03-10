<?php

namespace WHMCS\Module\Addon\InvoiceMerge\Service;

use WHMCS\Module\Addon\InvoiceMerge\Models\InvoiceItem;

class CreditInvoice extends BaseService
{
    public static function handle($request)
    {

        $data = [
            'userid' => $request['userid'],
            'itemamount1' => $request['amount'],
            'itemdescription1' => 'Add funds from dashboard.',
            'itemteaxed1' => '0',
        ];
        $result = localAPI('CreateInvoice', $data);
        if ($result['result'] != 'success') {
            return self::errorResponse(['error' => 'Could not create credit invoice.', 'message' => $result['message'] ?? 'error']);
        }

        $invoiceID = $result['invoiceid'];

        InvoiceItem::where('invoiceid', $invoiceID)->where('description', 'Add funds from dashboard.')->update(['type' => 'AddFunds']);
        return self::successResponse(['message' => 'Credit Service Created.', 'invoice_id' => $invoiceID]);
    }
}
