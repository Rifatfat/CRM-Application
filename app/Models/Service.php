<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'base_price'
    ];
    public function contracts()
    {       
    return $this->hasMany(Contract::class);
    }
}