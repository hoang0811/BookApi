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
        'co_publisher_id',
        'translator_id',
        'author_id',
        'category_id',
        'covertype_id',
        'genre_id',
        'language_id',
        'image',
        'description',
        'quantity',
        'original_price',
        'discount_price',
        'internal_code',
        'published_year',
        'published_date',
        'number_pages',
        'size',
        'weight',
        'keywords',
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

    // Liên kết đến CoPublisher
    public function coPublisher()
    {
        return $this->belongsTo(CoPublisher::class, 'co_publisher_id');
    }

    // Liên kết đến Translator
    public function translator()
    {
        return $this->belongsTo(Translator::class);
    }

    // Liên kết đến Author
    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    // Liên kết đến Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Liên kết đến CoverType
    public function coverType()
    {
        return $this->belongsTo(CoverType::class,'cover_type_id');
    }

    // Liên kết đến Genre
    public function genre()
    {
        return $this->belongsTo(Genres::class);
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

