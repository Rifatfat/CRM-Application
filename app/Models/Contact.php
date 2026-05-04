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

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}