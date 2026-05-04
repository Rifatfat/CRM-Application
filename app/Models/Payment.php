<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;
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