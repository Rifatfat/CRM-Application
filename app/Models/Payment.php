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

    public function scopeOwnedBy($query, int $userId)
    {
        return $query->whereHas('contract.client', function ($clientQuery) use ($userId) {
            $clientQuery->where('user_id', $userId);
        });
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

}
