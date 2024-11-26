<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Book extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'isbn',
        'publisher_id',
        'translator_id',
        'category_id',
        'cover_type_id',
        'genre_id',
        'language_id',
        'image',
        'description',
        'quantity',
        'original_price',
        'discount_price',
        'published_year',
        'number_pages',
        'length',
        'width',
        'height',
        'weight',
        'status',
    ];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($image) => url('/storage/books/' . $image),
        );
    }

    // Liên kết đến Publisher
    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }
    // Liên kết đến Translator
    public function translator()
    {
        return $this->belongsTo(Translator::class);
    }

    // Liên kết đến Author
    public function authors()
    {
        return $this->belongsToMany(Author::class, 'author_book', 'book_id', 'author_id');
    }

    // Liên kết đến Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Liên kết đến CoverType
    public function cover_type()
    {
        return $this->belongsTo(CoverType::class,'cover_type_id');
    }

    // Liên kết đến Genre
    public function genre()
    {
        return $this->belongsTo(Genre::class,'genre_id');
    }

    // Liên kết đến Language
    public function language()
    {
        return $this->belongsTo(Language::class);
    }
    // Liên kết đến Book Image
    public function images()
    {
        return $this->hasMany(BookImage::class);
    }

}

