<?php

namespace WHMCS\Module\Addon\InvoicePaid\Service;

use WHMCS\Module\Addon\InvoicePaid\Models\CustomField;

class SetCustomField extends BaseService
{

    public static function handle($request)
    {
        if (!self::validateRequest($request)) return self::errorResponse('validation error');

        $service = \WHMCS\Module\Addon\InvoicePaid\Models\Service::with('customFields')->where('id', $request['serviceid'])->first();
        $customNameField = self::getCustomField($service, $request['name'], $request['description'] ?? '');
        self::setCustomFieldValue($customNameField, $request['value']);
        return self::successResponse(['message' => 'OK']);
    }

    public static function validateRequest($request)
    {
        if (!isset($request['serviceid']) || !isset($request['name']) || !isset($request['value'])) return false;
        return true;
    }

    public static function getCustomField($service, $fieldName, $description)
    {
        $customFields = CustomField::where('relid', $service->id)->where('fieldname', $fieldName)->get();
        if ($customFields->count() === 1) {
            return $customFields->first();
        }
        if ($customFields->count() > 1) {
            $selectedField = $customFields->first();
            $deleteIDs = $customFields->where('relid', $service->id)->where('id', '!=', $selectedField->id)->pluck('id')->toArray();
            CustomField::whereIn('id', $deleteIDs)->delete();
            return $selectedField;
        }

        $service->customFields()->create([
            'type' => 'product',
            'fieldname' => $fieldName,
            'fieldtype' => 'text',
            'description' => $description,
        ]);
        return self::getCustomField($service, $fieldName, $description);
    }

    public static function setCustomFieldValue($customField, $value)
    {
        $customFieldValue = $customField->value;
        if ($customFieldValue) {
            $customFieldValue->value = $value;
            $customFieldValue->save();
            $customFieldValue->fresh();
            return $customFieldValue;
        }
        $customField->value()->create([
            'relid' => $customField->relid,
            'value' => $value
        ]);
        return $customField->value()->first();
    }
}
