<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_id',
        'contract_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'document_type',
        'uploaded_at'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
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

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
     public function user()
    {
    return $this->belongsTo(User::class, 'uploaded_by');
    }
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
    
}
