<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Publisher extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'image', 'country'];
    /**
     * image
     *
     * @return Attribute
     */
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($image) => url('/storage/publishers/' . $image),
        );
    }
    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
