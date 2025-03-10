<?php

include __DIR__ . '/lib/vendor/autoload.php';
include __DIR__ . '/lib/Helper/Helper.php';

use WHMCS\Module\Addon\InvoicePaid\Controllers\Admin\AdminDispatcher;
use WHMCS\Module\Addon\InvoicePaid\Controllers\Client\ClientDispatcher;
use WHMCS\Module\Addon\InvoicePaid\Service\Application;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

/**
 * InvoicePaid_config
 *
 * @return array
 */
function InvoicePaid_config(): array
{
    return Application::config();
}

/**
 * InvoicePaid_activate
 *
 * @return array
 */
function InvoicePaid_activate(): array
{
    return Application::activate();
}

/**
 * InvoicePaid_deactivate
 *
 * @return array
 */
function InvoicePaid_deactivate(): array
{
    return Application::deactivate();
}

