<?php

namespace WHMCS\Module\Addon\InvoiceMerge\Controllers\Client;

use Exception;
use WHMCS\Module\Addon\InvoiceMerge\Models\Client;
use WHMCS\Module\Addon\InvoiceMerge\Models\Invoice;
use WHMCS\Module\Addon\InvoiceMerge\Models\InvoiceItem;
use WHMCS\Module\Addon\InvoiceMerge\Service\Invoice\MassPayment;

/**
 * Sample Client Area Controller
 */
class ClientController
{

    public function cancell_invoice()
    {
        if (isset($_GET['invoice_id']) && is_numeric($_GET['invoice_id'])) {
            $invoiceId = $_GET['invoice_id'];
            $userId = $_SESSION['uid'];
            // check userId is equal to invoice userId
            $invoice = Invoice::find($invoiceId);
            if ($invoice->userid != $userId) {
                $_SESSION['whmcs_message'] = 'You are not authorized to cancel this invoice.';
                header('Location: clientarea.php?action=invoices');
                exit;
            }
            $result = Invoice::where('id', $invoiceId)->update(['status' => 'Cancelled']);
            foreach ($invoice->items as $item) {
                $invoice=Invoice::find($item->relid);
                $invoice->status = 'Unpaid';
                $invoice->save();
            }
            if ($result) {
                $_SESSION['whmcs_message_success'] = 'Invoice cancelled successfully!';
            } else {
                $_SESSION['whmcs_message_error'] = 'There was an issue cancelling the invoice.';
            }
            header('Location: clientarea.php?action=invoices');
            exit;
        }
    }



    public function mergeInvoices()
    {
        if (!isset($_POST['invoices_id']) || !is_array($_POST['invoices_id']) || count($_POST['invoices_id']) === 0) {
            $_SESSION['whmcs_message_error'] = 'No invoices selected for merging.';
            header('Location: clientarea.php?action=invoices');
            exit;
        }
    
        if (!isset($_SESSION['uid'])) {
            $_SESSION['whmcs_message_error'] = 'User not found.';
            header('Location: clientarea.php?action=invoices');
            exit;
        }
    
        $invoiceIds = array_map('intval', $_POST['invoices_id']);
        $userId = (int) $_SESSION['uid'];
    
        try {
            $hasAddFundsInvoice = InvoiceItem::whereIn('invoiceid', $invoiceIds)
                ->where('type', 'AddFunds')
                ->exists();
    
            if ($hasAddFundsInvoice) {
                $_SESSION['whmcs_message_error'] = 'Invoices containing "Add Funds" cannot be merged.';
                header('Location: clientarea.php?action=invoices');
                exit;
            }
    
            $unpaidInvoiceExists = Invoice::whereIn('id', function ($query) use ($userId) {
                    $query->select('invoiceid')
                        ->from('tblinvoiceitems')
                        ->where('type', 'Invoice')
                        ->where('userid', $userId);
                })
                ->whereIn('status', ['Unpaid','Payment Pending'])
                ->exists();
    
            if ($unpaidInvoiceExists) {
                $_SESSION['whmcs_message_error'] = 'There is an unpaid merged invoice. Please pay or cancel it first.';
                header('Location: clientarea.php?action=invoices');
                exit;
            }
    
            $data = [
                'userid' => $userId,
                'invoices' => $invoiceIds,
            ];
    
            $result = MassPayment::handle($data);
    
            if ($result['result'] === 'success') {
                foreach ($invoiceIds as $invoiceId) {
                    $invoice = Invoice::find($invoiceId);
                    $invoice->status = 'Packed Invoice';
                    $invoice->save();
                }
                $_SESSION['whmcs_message_success'] = 'Invoices merged successfully!';
            } else {
                $_SESSION['whmcs_message_error'] = $result['message'];
            }
        } catch (Exception $e) {
            $_SESSION['whmcs_message_error'] = 'An error occurred: ' . htmlspecialchars($e->getMessage());
        }
    
        header('Location: clientarea.php?action=invoices');
        exit;
    }
    
}
