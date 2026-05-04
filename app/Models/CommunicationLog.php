<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommunicationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'communication_type',
        'notes',
        'communication_date'
    ];

    protected $casts = [
        'communication_date' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
