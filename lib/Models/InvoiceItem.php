<?php

namespace WHMCS\Module\Addon\InvoicePaid\Models;

if (!defined('WHMCS')) {
    die('This file cannot be access directly!');
}

use WHMCS\Database\Capsule;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $table = 'tblinvoiceitems';
    public $timestamps = false;

    protected $fillable = [
        'invoiceid',
        'type',
        'amount',
        'userid',
        'description',
        'taxed',
        'duedate',
        'relid'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Get Client Product
     *
     * @return mixed
     */
    public function clientProduct(): mixed
    {
        return $this->hasOne(ClientProduct::class, 'id', 'relid');
    }


    /**
     * Get Invoice
     *
     * @return mixed
     */
    public function invoice(): mixed
    {
        return $this->hasOne(Invoice::class, 'id', 'invoiceid');
    }
}
