<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;
    public function province()
    {
        return $this->belongsTo(Province::class);
    }
    public function ward()
    {
        return $this->hasMany(Ward::class);
    }
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
