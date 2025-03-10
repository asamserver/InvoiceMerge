<?php

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly!');
}

use WHMCS\Module\Addon\InvoiceMerge\Models\Setting;


try {
    // Get validated POST data
    $request = $_POST;

    // Retrieve the module setting
    $setting = Setting::where('key', 'api_access')->where('type', 'option')->first();

    if (empty($setting)) {
        throw new Exception('Module is deactivated. Please check the active checkbox in the module api access settings page.');
    }

    // Check if the module is active
    if ($setting->value != 'active') {
        throw new Exception('Module is deactivated. Please check the active checkbox in the module api access settings page.');
    }

    $function = "\WHMCS\Module\Addon\InvoiceMerge\Service\\" . $request['function'];
    if (class_exists($function)) {
        $apiresults = $function::handle($request);
    } else {
        $apiresults = [
            'result' => 'error',
            'message' => 'function not found',
        ];
    }
} catch (Exception $e) {
    // Handle any validation or processing errors
    $apiresults = [
        'result' => 'error',
        'message' => $e->getMessage(),
        'location' => 'InvoiceMergeapi.php'
    ];
}
