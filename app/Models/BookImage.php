<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class BookImage extends Model
{
    use HasFactory;

    protected $fillable=[
        'book_id',
        'path'
    ];
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($path) => url('/storage/book_images/' . $path),
        );
    }
}
