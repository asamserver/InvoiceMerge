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
        if (isset($_POST['invoices_id']) && is_array($_POST['invoices_id']) && count($_POST['invoices_id']) > 0) {
            $invoiceIds = $_POST['invoices_id'];

            $data = [
                'userid' => $_SESSION['uid'],
                'invoices' => $invoiceIds
            ];

            try {


                foreach ($invoiceIds as $invoiceId) {
                    $type = Invoice::find($invoiceId);
                    if ($type->type == 'AddFunds') {
                        $_SESSION['whmcs_message_error'] = 'There is unpaid merged invoice. Please pay or cancel it first.';
                        die("JHSDJHGDB");
                        header('Location: clientarea.php?action=invoices');
                        exit;
                    }
                }
                if (!isset($_SESSION['uid'])) {
                    $_SESSION['whmcs_message_error'] = 'Use not found';
                    header('Location: clientarea.php?action=invoices');
                    exit;
                }
                $invoiceItems = InvoiceItem::where('type', 'Invoice')
                    ->where(['userid', $_SESSION['uid']])
                    ->get();

                foreach ($invoiceItems as $invoiceItem) {
                    $invoices = Invoice::where(['id' => $invoiceItem->invoiceid, 'status' => 'Unpaid'])->first();
                    if ($invoices) {
                        break;
                    }
                }
                if ($invoices) {
                    $_SESSION['whmcs_message_error'] = 'There is unpaid merged invoice. Please pay or cancel it first.';
                    header('Location: clientarea.php?action=invoices');
                    exit;
                }
                // var_dump($data);
                $result = MassPayment::handle($data);
                // var_dump($result );
                // die("asdhjkauhs");
                if ($result['result'] == 'success') {
                    $_SESSION['whmcs_message_success'] = 'Invoices merged successfully!';
                } else {
                    $_SESSION['whmcs_message_error'] = $result['message'];
                }
            } catch (Exception $e) {
                $_SESSION['whmcs_message_error'] = 'An error occurred: ' . htmlspecialchars($e->getMessage());
            }

            header('Location: clientarea.php?action=invoices');
            exit;
        } else {
            $_SESSION['whmcs_message_error'] = 'No invoices selected for merging.';
            header('Location: clientarea.php?action=invoices');
            exit;
        }
    }
}
