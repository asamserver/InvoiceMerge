<?php

namespace WHMCS\Module\Addon\InvoicePaid\Models;

if (!defined('WHMCS')) {
    die('This file cannot be access directly!');
}

use WHMCS\Database\Capsule;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'tblinvoices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

   
    public function items(): mixed
    {
        return $this->hasMany(InvoiceItem::class, 'invoiceid', 'id');
    }

    public function client(): mixed
    {
        return $this->belongsTo(Client::class, 'userid');
    }
}
