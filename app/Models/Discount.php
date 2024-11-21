<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;
    protected $fillable=[
        'code',
        'discount_type',
        'discount_value',
        'cart_value',
        'start_date',
        'end_date',
        'usage_limit',
        'is_active',
    ];
}