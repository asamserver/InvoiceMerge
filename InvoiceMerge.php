<?php

include __DIR__ . '/lib/vendor/autoload.php';
include __DIR__ . '/lib/Helper/Helper.php';

use WHMCS\Module\Addon\InvoiceMerge\Controllers\Admin\AdminDispatcher;
use WHMCS\Module\Addon\InvoiceMerge\Controllers\Client\ClientDispatcher;
use WHMCS\Module\Addon\InvoiceMerge\Service\Application;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

/**
 * InvoiceMerge_config
 *
 * @return array
 */
function InvoiceMerge_config(): array
{
    return Application::config();
}

/**
 * InvoiceMerge_activate
 *
 * @return array
 */
function InvoiceMerge_activate(): array
{
    return Application::activate();
}

/**
 * InvoiceMerge_deactivate
 *
 * @return array
 */
function InvoiceMerge_deactivate(): array
{
    return Application::deactivate();
}
