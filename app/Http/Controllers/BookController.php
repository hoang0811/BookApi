<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books =  Book::with('images')->orderBy('id', 'DESC')->paginate(10);
        return new BookResource(true, 'Data retrieved successfully', $books);

    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books',
            'publisher_id' => 'nullable|exists:publishers,id',
            'co_publisher_id' => 'nullable|exists:co_publishers,id',
            'translator_id' => 'nullable|exists:translators,id',
            'author_id' => 'nullable|exists:authors,id',
            'category_id' => 'nullable|exists:categories,id',
            'cover_type_id' => 'nullable|exists:cover_types,id',
            'genre_id' => 'nullable|exists:genres,id',
            'language_id' => 'nullable|exists:languages,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'required|string',
            'quantity' => 'nullable|integer|min:0',
            'original_price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'internal_code' => 'required|string',
            'published_year' => 'required|integer',
            'published_date' => 'required|date',
            'number_pages' => 'required|integer',
            'size' => 'required|string',
            'weight' => 'required|numeric|min:0',
            'keywords' => 'nullable|string',
            'status' => 'nullable|in:instock,out_of_stock,pre_order',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image = $request->file('image');
        $imagePath = $image->storeAs('books', $image->hashName(), 'public');

        $book = Book::create(array_merge($request->all(), ['image' => baseName($imagePath)]));
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $images) {
                $path = $images->storeAs('book_images', $images->hashName(), 'public');
                
                $book->images()->create([
                    'path' => basename($path),
                ]);
            }
        }

        return new BookResource(true, 'Book created successfully', $book);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $book = Book::with('images')->findOrFail($id);
        return new BookResource(true,'Detail Data Book ',$book);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn,' . $id,
            'publisher_id' => 'nullable|exists:publishers,id',
            'co_publisher_id' => 'nullable|exists:co_publishers,id',
            'translator_id' => 'nullable|exists:translators,id',
            'author_id' => 'nullable|exists:authors,id',
            'category_id' => 'nullable|exists:categories,id',
            'cover_type_id' => 'nullable|exists:cover_types,id',
            'genre_id' => 'nullable|exists:genres,id',
            'language_id' => 'nullable|exists:languages,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'required|string',
            'quantity' => 'nullable|integer|min:0',
            'original_price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'internal_code' => 'required|string',
            'published_year' => 'required|integer',
            'published_date' => 'required|date',
            'number_pages' => 'required|integer',
            'size' => 'required|string',
            'weight' => 'required|numeric|min:0',
            'keywords' => 'nullable|string',
            'status' => 'nullable|in:instock,out_of_stock,pre_order',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        $book = Book::findOrFail($id);
    
        if ($request->hasFile('image')) {
            if ($book->image && Storage::exists('public/books/' . $book->image)) {
                Storage::delete('public/books/' . $book->image);
            }
            $image = $request->file('image');
            $imagePath = $image->storeAs('books', $image->hashName(), 'public');
            $book->image = basename($imagePath);
        }
        
        if ($request->hasFile('images')) {
            foreach ($book->images as $img) {
                Storage::delete('public/book_images/' . $img->path);
            }
                $book->images()->delete();
    
            foreach ($request->file('images') as $images) {
                $path = $images->storeAs('book_images', $images->hashName(), 'public');
                $book->images()->create([
                    'path' => basename($path),
                ]);
            }
        }
    
        $book->update($request->except('image', 'images'));
    
        return new BookResource(true, 'Book updated successfully', $book);
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $book = Book::findOrFail($id);
    
        if (Storage::exists('public/books/' . basename($book->image))) {
            Storage::delete('public/books/' . basename($book->image));
        }
        $bookImages = $book->images;
        foreach ($bookImages as $bookImage) {
            if (Storage::exists('public/book_images/' . basename($bookImage->path))) {
                Storage::delete('public/book_images/' . basename($bookImage->path));
            }
            $bookImage->delete();
        }
    
        $book->delete();
    
        return new BookResource(true, 'Book deleted successfully', null);
    }

}
