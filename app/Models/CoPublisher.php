<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;


class CoPublisher extends Model
{
    use HasFactory;
    protected $fillable = ['name','image'];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($image) => url('/storage/co_publishers/' . $image),
        );
    }
    public function books()
    {
        return $this->hasMany(Book::class, 'co_publisher_id');
    }
}
