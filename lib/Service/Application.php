<?php

namespace WHMCS\Module\Addon\InvoiceMerge\Service;

if (!defined('WHMCS')) {
    die('This file cannot be access directly!');
}

use Exception;
use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\InvoiceMerge\Models\Setting;

class Application
{
    /**
     * initial configuration for module
     *
     * @return string[]
     */
    public static function config(): array
    {
        return [
            'name' => 'InvoiceMerge',
            'description' => 'Jump panel extra config manager <script type="text/javascript"> $(document).ready(function(){ $("td > a[name=InvoiceMerge]").prepend("<img width=\"40px\" height=\"40px\" src=\"../modules/addons/InvoiceMerge/logo.png\">"); }); </script>',
            'author' => 'Invoice Merge',
            'language' => 'english',
            'version' => '1.0',
            "fields" => [
                "whmcs_dir_url" => [
                    "FriendlyName" => "Whmcs DIR URL",
                    "Type" => "text",
                    "Size" => "255",
                    "Description" => "Textbox",
                    "Default" => "https://whmcs.as-test.com/"
                ],
                "showPackTable" => [
                    "FriendlyName" => "Show Pack Table",
                    "Type" => "yesno",
                    "Size" => "25",
                    "Description" => "Show Pack Table",
                    "Default" => false
                ]

            ]
        ];
    }

    /**
     * activate module
     *
     * @return string[]
     */
    public static function activate(): array
    {
        try {
            self::createCustomApi();

            return [
                'status' => 'success',
                'description' => 'Wam Jump module activated successfully',
            ];
        } catch (Exception $e) {
            logActivity("Addons Module Wam Jump activate : {$e->getMessage()}");

            return [
                'status' => 'error',
                'description' => 'Unable to activate module: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * deactivate module
     *
     * @return string[]
     */
    public static function deactivate(): array
    {
        try {
            self::deleteFile(self::apiDest());

            /*
             * commented for now
            $deleteSetting = InvoiceMerge_getSetting('delete_data', 'env');

            if (!empty($deleteSetting)) {
                if ($deleteSetting == 'false') {
                    return [
                        'status' => 'success',
                        'description' => 'module deactivated successfully without deleting data.',
                    ];
                }
            }
            */

            //            Capsule::schema()->dropIfExists('mod_wam_jump_settings');

            // Commented role creation and deletion for now
            //            Capsule::table('tblapi_roles')->where('role', 'InvoiceMerge')->delete();

            return [
                'status' => 'success',
                'description' => 'module deactivated successfully without deleting data.',
            ];
        } catch (Exception $e) {
            logActivity("Addons Module Wam Jump deactivate module: {$e->getMessage()}");

            return [
                'status' => 'error',
                'description' => "Unable to deactivated module: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Create Custom Api
     *
     * @return void
     */
    public static function createCustomApi()
    {
        self::deleteFile(self::apiDest());

        // Commented role creation and deletion for now
        Capsule::table('tblapi_roles')->insert([
            'role' => 'InvoiceMerge',
            'description' => 'access to "InvoiceMergeApi" action',
            'permissions' => json_encode(['InvoiceMergeapi' => 1]),
        ]);

        if (copy(self::apiSrc(), self::apiDest())) {
            logActivity('Addons Module InvoiceMerge api file added.');
        } else {
            logActivity('Addons Module InvoiceMerge cannot add api custom file');
        }
    }

    /**
     * Api delete file
     *
     * @return string
     */
    private static function deleteFile(string $src)
    {
        if (file_exists($src)) {
            unlink($src);
        }
    }

    /**
     * Api file source path
     *
     * @return string
     */
    private static function apiSrc(): string
    {
        return __DIR__ . '/Api/InvoiceMergeapi.php';
    }

    /**
     * Api file destination path
     *
     * @return string
     */
    private static function apiDest(): string
    {
        return __DIR__ . '/../../../../../includes/api/InvoiceMergeapi.php';
    }
}
