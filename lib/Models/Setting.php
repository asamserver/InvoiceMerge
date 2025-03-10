<?php

namespace WHMCS\Module\Addon\InvoicePaid\Models;

if (!defined('WHMCS')) {
    die('This file cannot be access directly!');
}

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mod_wam_jump_settings';

    /**
     * disable timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * cast value type
     *
     * @return mixed
     */
    public function getValueAttribute(): mixed
    {
        return $this->attributes['value'];
    }
}
