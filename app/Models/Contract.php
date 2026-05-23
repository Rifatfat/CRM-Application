<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contract extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_id',
        'service_id',
        'contract_value',
        'start_date',
        'end_date',
        'status'
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

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function communicationLogs()
    {
        return $this->hasMany(CommunicationLog::class);
    }
}
