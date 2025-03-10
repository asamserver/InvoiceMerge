<?php

include __DIR__ . '/lib/vendor/autoload.php';
include __DIR__ . '/lib/Helper/Helper.php';

\WHMCS\Module\Addon\InvoicePaid\Service\Hook::InvoicePaid_register();
