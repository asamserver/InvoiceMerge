<?php

namespace WHMCS\Module\Addon\InvoiceMerge\Service;

abstract class BaseService
{
    public static function errorResponse($message)
    {
        return [
            'result' => 'error',
            'message' => $message,
        ];
    }

    public static function successResponse($data = [])
    {
        return [
            'result' => 'success',
        ] + $data;
    }
}
