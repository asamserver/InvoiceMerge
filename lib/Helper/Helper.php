<?php

if (!defined('WHMCS')) {
    die('This file cannot be access directly!');
}

use WHMCS\Module\Addon\InvoiceMerge\Models\Setting;
use WHMCS\Database\Capsule;

/**
 * Get Setting
 *
 * @param string $key
 * @param string $type
 * @return string|null
 */
if (!function_exists('InvoiceMerge_getSetting')) {
    function InvoiceMerge_getSetting(string $key, string $type): ?string
    {
        $setting = Setting::where('key', "$key")->where('type', $type)->first();

        if (empty($setting)) {
            return null;
        }

        return $setting->value;
    }
}

/**
 * Update Setting
 *
 * @param string $key
 * @param string|null $value
 * @return Setting|null
 */
if (!function_exists('InvoiceMerge_updateSetting')) {
    function InvoiceMerge_updateSetting(string $key, string $value = null): ?Setting
    {
        $setting = Setting::where('key', "$key")->first();

        if (empty($setting)) {
            return null;
        }

        $setting->value = $value;
        $setting->save();

        return $setting;
    }
}

/**
 * Store Setting
 *
 * @param string $key
 * @param string $type
 * @param string|null $value
 * @return Setting|null
 */
if (!function_exists('InvoiceMerge_storeSetting')) {
    function InvoiceMerge_storeSetting(string $key, string $type, string $value = null): ?Setting
    {
        $checkSetting = Setting::where('key', "$key")->first();

        if (!empty($checkSetting)) {
            return null;
        }

        $setting = new Setting();
        $setting->key = "$key";
        $setting->type = $type;
        $setting->value = $value;
        $setting->save();

        return $setting;
    }
}

/**
 * Delete Setting
 *
 * @param string $key
 * @return bool
 */
if (!function_exists('InvoiceMerge_deleteSetting')) {
    function InvoiceMerge_deleteSetting(string $key): bool
    {
        $setting = Setting::where('key', "$key")->first();

        if (empty($setting)) {
            return false;
        }

        $setting->delete();

        return true;
    }
}

/**
 * send data o included php file inside View folder
 *
 * @param string $path
 * @param array  $data
 *
 * @return string
 */
if (!function_exists('InvoiceMerge_renderAdminView')) {
    function InvoiceMerge_renderAdminView(string $path, array $data = []): string
    {
        $path = __DIR__ . '/../View/admin/' . $path . '.php';
        $view = '';
        if (file_exists($path)) {
            extract($data);

            ob_start();

            include $path;

            $view = ob_get_clean();
        }

        return $view;
    }
}

/**
 * render js script files
 *
 * @param string $path
 *
 * @return string
 */
if (!function_exists('InvoiceMerge_renderJS')) {
    function InvoiceMerge_renderJS(string $path, string $type = 'local'): string
    {
        if ($type == 'cdn') {
            return '<script src="' . $path . '"></script>';
        }

        $whmcsDirUrlSetting = InvoiceMerge_getSetting('whmcs_dir_url', 'env');
        if (!empty($whmcsDirUrlSetting)) {
            $path = $whmcsDirUrlSetting . '/modules/addons/InvoiceMerge/assets/js/' . $path;
        } else {
            $path = '/modules/addons/InvoiceMerge/assets/js/' . $path;
        }

        $generateRandomNumber = InvoiceMerge_generateRandomNumber();

        return '<script type="text/javascript" src="' . $path . '?v=' . $generateRandomNumber . '"></script>';
    }
}

/**
 * render js script files
 *
 * @param string $path
 *
 * @return string
 */
if (!function_exists('InvoiceMerge_renderCSS')) {
    function InvoiceMerge_renderCSS(string $path, string $type = 'local'): string
    {
        if ($type == 'cdn') {
            return '<link rel="stylesheet" href="' . $path . '">';
        }

        $whmcsDirUrlSetting = InvoiceMerge_getSetting('whmcs_dir_url', 'env');
        if (!empty($whmcsDirUrlSetting)) {
            $path = $whmcsDirUrlSetting . '/modules/addons/InvoiceMerge/assets/css/' . $path;
        } else {
            $path = '/modules/addons/InvoiceMerge/assets/css/' . $path;
        }

        $generateRandomNumber = InvoiceMerge_generateRandomNumber();

        return '<link rel="stylesheet" type="text/css" href="' . $path . '?v=' . $generateRandomNumber . '">';
    }
}

/**
 * generateRandomNumber
 *
 * @param string $path
 *
 */
if (!function_exists('InvoiceMerge_generateRandomNumber')) {
    function InvoiceMerge_generateRandomNumber(): int
    {
        // Get the current time in microseconds
        $microtime = microtime(true);

        // Use the microtime to seed the random number generator
        mt_srand($microtime);

        // Generate a random number
        $randomNumber = mt_rand();

        return $randomNumber;
    }
}

/**
 * get client_id by user_id
 *
 * @param $userId
 *
 * @return mixed
 */
if (!function_exists('InvoiceMerge_getClientId')) {
    function InvoiceMerge_getClientId($userId = null)
    {
        if ($_SESSION['uid']) {
            return $_SESSION['uid'];
        }
        $userId = $userId ?? json_decode($_SESSION['login_auth_tk'], true)['id'];

        return Capsule::table('tblusers_clients')->where('auth_user_id', $userId)->first()->client_id;
    }
}

/**
 * Get Controller Class
 *
 * @param string $controllerName
 * @return string
 */
if (!function_exists('InvoiceMerge_getControllerClass')) {
    function InvoiceMerge_getControllerClass(string $controllerName): string
    {
        // Return the fully qualified class name
        return "WHMCS\\Module\\Addon\\InvoiceMerge\\Controllers\\Admin\\" . ucfirst($controllerName) . "Controller";
    }
}
