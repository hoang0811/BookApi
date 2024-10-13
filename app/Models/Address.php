<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    // Các thuộc tính có thể gán
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'email',
        'district_id',
        'ward_id',
        'province_id',
        'street',
        'address_type',
        'is_default',
    ];

    // Các thuộc tính không thể gán
    protected $guarded = ['id', 'created_at', 'updated_at'];

    // Quan hệ với mô hình User
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function district()
    {
        return $this->belongsTo(District::class);
    }


    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }


    public function province()
    {
        return $this->belongsTo(Province::class);
    }
}
