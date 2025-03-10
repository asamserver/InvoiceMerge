<?php

namespace WHMCS\Module\Addon\InvoiceMerge\Models;

if (!defined('WHMCS')) {
    die('This file cannot be access directly!');
}

use WHMCS\User\Client as BaseClient;

class Client extends BaseClient
{
    /**
     * The attributes that should be appends.
     *
     * @var array<string, string>
     */
    protected $appends = ['fullname'];

    /**
     * Get Full Name Attribute
     *
     * @return string|null
     */
    public function getFullnameAttribute(): ?string
    {
        if ($this->firstname || $this->lastname) {
            return trim("$this->firstname $this->lastname");
        }
        return null;
    }
}
