<?php

namespace WHMCS\Module\Addon\InvoiceMerge\Controllers\Client;

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly!');
}

class ClientDispatcher
{

    /**
     * Dispatch request.
     *
     * @param string $action
     * @param array $parameters
     *
     * @return array
     */
    public function dispatch($action, $parameters)
    {
        if (!$action) {
            // Default to index if no action specified
            $action = 'index';
        }

        $controller = new ClientController();

        // Verify requested action is valid and callable
        if (is_callable(array($controller, $action))) {
            return $controller->$action($parameters);
        }
    }
}
