<?php

namespace WHMCS\Module\Addon\InvoiceMerge\Service;

use Exception;
use WHMCS\Module\Addon\InvoiceMerge\Models\Invoice;
use WHMCS\Module\Addon\InvoiceMerge\Models\InvoiceItem;
use WHMCS\Database\Capsule;

if (!defined('WHMCS')) {
    die('This file cannot be access directly!');
}

class Hook
{
    /**
     * add all hooks in this method to run
     *
     * @return void
     */
    public static function InvoiceMerge_register(): void
    {
        self::InvoiceMerge_addInvoiceCheckboxes();
    }


    public static function InvoiceMerge_addInvoiceCheckboxes()
    {


        add_hook("ClientAreaPage", 1, function ($vars) {
            $results = Capsule::table('tbladdonmodules')->where('module', 'InvoiceMerge')->pluck('value', 'setting')->toArray();
            if (strpos($_SERVER['REQUEST_URI'], 'clientarea.php?action=invoices') !== false && $results['showPackTable'] == 'on') {
                $clientId = $_SESSION['uid'] ?? null;
                if (!$clientId) {
                    return;
                }
                $packs = InvoiceItem::where('type', 'Invoice')
                    ->where('userid', $clientId)
                    ->whereHas('invoice', function ($query) {
                        $query->whereIn('status', ['Unpaid', 'Paid']);
                    })
                    ->get();

                $packs_data = [];
                $unpaidInvoices = [];

                foreach ($packs as $pack) {
                    $inv = Invoice::find($pack->invoiceid);

                    if ($inv->status == 'Unpaid' || $inv->status == 'Payment Pending') {
                        $unpaidInvoices[$pack->invoiceid] = $inv;
                    } elseif ($inv->status == 'Paid') {
                        $packs_data[$pack->invoiceid] = $inv;
                    }
                }
                var_dump($unpaidInvoices);
                

                $script = '
                    <script>
                   function addCheckboxes() {
                    if (!document.querySelector("#tableInvoicesList thead tr th:first-child").textContent.includes("select")) {
                        const tableHeader = document.querySelector("#tableInvoicesList thead tr");
                        const th = document.createElement("th");
                        th.innerHTML = "select";
                        th.style.width = "0px";                    
                        tableHeader.insertBefore(th, tableHeader.firstChild);
                    }
                    
                    document.querySelectorAll("#tableInvoicesList tbody tr").forEach(function(row) {
                        if (!row.querySelector("td:first-child input[type=checkbox]")) {
                            var invoiceId = row.querySelector("td:nth-child(1)").textContent.trim();
                            var statusElement = row.querySelector("td:nth-child(5)"); 
                            var status = statusElement ? statusElement.textContent.trim().toLowerCase() : "";
                            var td = document.createElement("td");
                            var checkbox = document.createElement("input");
                            checkbox.type = "checkbox";
                            checkbox.className = "invoice-check text-center invoice-checkbox"; 
                            checkbox.value = invoiceId;
                            if (status.includes("unpaid")) {
                                checkbox.disabled = false;
                            } else {
                                checkbox.disabled = true;
                            }
                            td.appendChild(checkbox);
                            row.insertBefore(td, row.firstChild);
                            checkbox.addEventListener("click", function(e) {
                                e.stopPropagation();
                            });
                        }
                    });
                }


            
                            window.onload = function() {
                                const table = document.querySelector("#tableInvoicesList");
                                observer.observe(table, { childList: true, subtree: true });
                                addCheckboxes();
                                insertUnpaidInvoices();
                                insertPackedInvoices();
                                addMergeButton();

                                var messageDiv = document.querySelector(".header-lined");
                                messageDiv.innerHTML = `' . display_message() . '`;
                            };
            
                            const observer = new MutationObserver(function(mutations) {
                                mutations.forEach(function(mutation) {
                                    if (mutation.type === "childList" && 
                                        (mutation.target.id === "tableInvoicesList" || 
                                        mutation.target.tagName === "TBODY")) {
                                        addCheckboxes();
                                    }
                                });
                            });
                            
            
                            function addMergeButton() {
                                const mergeBtn = document.createElement("button");
                                mergeBtn.innerText = "Merge Selected Invoices";
                                mergeBtn.classList.add("btn", "btn-primary");
                                mergeBtn.style.marginTop = "10px";
                                mergeBtn.style.marginBottom = "20px";
                                mergeBtn.onclick = function() {
                                    let selectedInvoices = [];
                                    document.querySelectorAll(".invoice-checkbox:checked").forEach(checkbox => {
                                        selectedInvoices.push(checkbox.value);
                                    });
            
                                    if (selectedInvoices.length > 0) {
                                        const form = document.createElement("form");
                                        form.method = "POST";
                                        form.action = "index.php?m=InvoiceMerge&action=mergeInvoices";
            
                                        selectedInvoices.forEach(id => {
                                            const input = document.createElement("input");
                                            input.type = "hidden";
                                            input.name = "invoices_id[]";
                                            input.value = id;
                                            form.appendChild(input);
                                        });
            
                                        document.body.appendChild(form);
                                        form.submit();
                                    } else {
                                        alert("Please select at least one invoice to merge.");
                                    }
                                };
            
                                document.querySelector("#tableInvoicesList").insertAdjacentElement("afterend", mergeBtn);
                            }

                       function insertUnpaidInvoices() {
                            let unpaidInvoices = ' . json_encode($unpaidInvoices) . ';
                            if (Object.keys(unpaidInvoices).length === 0) {
                                return; // Exit the function early if there are no unpaid invoices
                            }

                            let invoiceWrapper = document.createElement("div");
                            let title = document.createElement("h4");
                            title.textContent = "Unpaid Pack";
                            title.style.marginBottom = "10px";
                            title.style.fontWeight = "bold";
                            invoiceWrapper.appendChild(title);

                            for (let invoiceId in unpaidInvoices) {
                                let invoice = unpaidInvoices[invoiceId];

                                let invoiceBox = document.createElement("div");
                                invoiceBox.style.border = "1px solid #ccc";
                                invoiceBox.style.padding = "25px";
                                invoiceBox.style.backgroundColor = "transparent";
                                invoiceBox.style.width = "100%";
                                invoiceBox.style.margin = "10px 0";
                                invoiceBox.style.display = "flex";
                                invoiceBox.style.justifyContent = "space-between";
                                invoiceBox.style.alignItems = "center"; 

                                let invoiceDetails = document.createElement("span");
                                invoiceDetails.innerHTML = `
                                    <strong>Invoice #:</strong> ${invoiceId} |
                                    <strong>Created At:</strong> ${invoice.duedate} |
                                    <strong>Total:</strong> $${invoice.total} USD
                                `;

                                let buttonContainer = document.createElement("div"); // Ensure this is defined outside the if-else

                                if (invoice.status == "Payment Pending") {
                                    let payButton = document.createElement("div");
                                    payButton.textContent = "Payment Pending";
                                    payButton.style.border = "2px solid #ccc";
                                    payButton.style.color = "blue";
                                    payButton.style.padding = "5px 30px";
                                    payButton.style.textDecoration = "none";
                                    payButton.style.marginRight = "10px";
                                    payButton.style.borderRadius = "3px";

                                    buttonContainer.appendChild(payButton);
                                } else {
                                    let payButton = document.createElement("a");
                                    payButton.href = `viewinvoice.php?id=${invoiceId}`;
                                    payButton.textContent = "Pay";
                                    payButton.style.border = "2px solid #ccc";
                                    payButton.style.color = "green";
                                    payButton.style.padding = "5px 30px";
                                    payButton.style.textDecoration = "none";
                                    payButton.style.marginRight = "10px";
                                    payButton.style.borderRadius = "3px";

                                    let cancelButton = document.createElement("a");
                                    cancelButton.href = `index.php?m=InvoiceMerge&action=cancell_invoice&invoice_id=${invoiceId}`;
                                    cancelButton.textContent = "Cancel";
                                    cancelButton.style.border = "2px solid #ccc";
                                    cancelButton.style.color = "red";
                                    cancelButton.style.padding = "5px 30px";
                                    cancelButton.style.borderRadius = "3px";
                                    cancelButton.style.textDecoration = "none";

                                    buttonContainer.appendChild(payButton);
                                    buttonContainer.appendChild(cancelButton);
                                }

                                invoiceBox.appendChild(invoiceDetails);
                                invoiceBox.appendChild(buttonContainer); // Ensure buttonContainer is appended
                                invoiceWrapper.appendChild(invoiceBox);
                            }

                            document.querySelector("#tableInvoicesList_wrapper").insertAdjacentElement("beforebegin", invoiceWrapper);
                        }


                        function insertPackedInvoices() {
                            let packs_data = ' . json_encode($packs_data) . ';
                            if (Object.keys(packs_data).length === 0) {
                                return;
                            }
                            let invoiceWrapper = document.createElement("div");
                            invoiceWrapper.style.marginTop="20px";
                            let title = document.createElement("h4");
                            title.textContent = "Paid Packs";
                            title.style.marginBottom = "10px";
                            title.style.fontWeight = "bold";
                            invoiceWrapper.appendChild(title);
                            for (let invoiceId in packs_data) {
                                let invoice = packs_data[invoiceId];
                                let invoiceBox = document.createElement("div");
                                invoiceBox.style.border = "1px solid #ccc";
                                invoiceBox.style.padding = "18px";
                                invoiceBox.style.backgroundColor = "transparent";
                                invoiceBox.style.width = "100%";
                                invoiceBox.style.margin = "10px 0";
                                invoiceBox.style.display = "flex";
                                invoiceBox.style.justifyContent = "space-between";
                                invoiceBox.style.alignItems = "center"; 
                                let invoiceDetails = document.createElement("span");
                                invoiceDetails.innerHTML = `
                                    <strong>Invoice #:</strong> ${invoiceId} |
                                    <strong>Created At:</strong> ${invoice.duedate} |
                                    <strong>Total:</strong> $${invoice.total === "0.00" ? invoice.subtotal : invoice.total} USD
                                `;

                                let viewButton = document.createElement("a");
                                viewButton.href = `viewinvoice.php?id=${invoiceId}`;
                                viewButton.textContent = "Paid";
                                viewButton.style.border = "2px solid #ccc";
                                viewButton.style.color = "green";
                                viewButton.style.padding = "5px 30px";
                                viewButton.style.borderRadius = "3px";
                                viewButton.style.textDecoration = "none";

                                invoiceBox.appendChild(invoiceDetails);
                                invoiceBox.appendChild(viewButton);

                                invoiceWrapper.appendChild(invoiceBox);
                            }

                            document.querySelector("#tableInvoicesList_length").insertAdjacentElement("afterend", invoiceWrapper);
                        }



                    </script>
                ';

                echo $script;
            }
        });


        function display_message()
        {
            if ($_SESSION["whmcs_message_error"]) {
                $message = "<div class=\"alert alert-danger\" role=\"alert\">" . $_SESSION["whmcs_message_error"] . "</div>";
                unset($_SESSION["whmcs_message_error"]);
            } elseif ($_SESSION["whmcs_message_success"]) {
                $message = "<div class=\"alert alert-success\" role=\"alert\">" . $_SESSION["whmcs_message_success"] . "</div>";
                unset($_SESSION["whmcs_message_success"]);
            } else {
                $message = null;
            }
            return $message;
        }
    }
}
