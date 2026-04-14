<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'contract_id',
        'amount',
        'payment_date',
        'payment_method',
        'status'
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}