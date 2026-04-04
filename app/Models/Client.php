<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'industry',
        'address',
        'notes'
    ];

    // RELATIONSHIP

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function communicationLogs()
    {
        return $this->hasMany(CommunicationLog::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}