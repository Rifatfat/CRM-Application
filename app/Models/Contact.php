<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'position',
        'email',
        'phone'
    ];

    public function scopeOwnedBy($query, int $userId)
    {
        return $query->whereHas('client', function ($clientQuery) use ($userId) {
            $clientQuery->where('user_id', $userId);
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
