<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'client_id',
        'contract_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'document_type',
        'uploaded_at'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}