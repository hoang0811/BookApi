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
        $books =  Book::all();
        return new BookResource(true, 'Data retrieved successfully', $books);

    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books',
            'publisher_id' => 'required|exists:publishers,id',
            'translator_id' => 'nullable|exists:translators,id',
            'category_id' => 'required|exists:categories,id',
            'cover_type_id' => 'required|exists:cover_types,id',
            'genre_id' => 'required|exists:genres,id',
            'language_id' => 'required|exists:languages,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'required|string',
            'quantity' => 'required|integer|min:0',
            'original_price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'published_year' => 'required|integer',
            'number_pages' => 'required|integer',
         'length' => 'required|numeric|min:0',
         'width' => 'required|numeric|min:0',
         'height' => 'required|numeric|min:0',
            'weight' => 'required|numeric|min:0',
            'status' => 'required|in:instock,out_of_stock,pre_order',
            'authors' => 'required|array',
            'authors.*' => 'exists:authors,id', // Kiểm tra danh sách tác giả
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        $image = $request->file('image');
        $imagePath = $image->storeAs('books', $image->hashName(), 'public');
    
        $book = Book::create(array_merge($request->all(), ['image' => basename($imagePath)]));
    
        $book->authors()->sync($request->authors);
    
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $images) {
                $path = $images->storeAs('book_images', $images->hashName(), 'public');
                $book->images()->create(['path' => basename($path)]);
            }
        }
    
        return new BookResource(true, 'Book created successfully', $book);
    }
    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $book = Book::with([
            'images', 'publisher', 'genre', 'category', 'language', 'cover_type', 'translator', 'authors'
        ])->findOrFail($id);
    
        return new BookResource(true, 'Detail Data Book', $book);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn,' . $id,
            'publisher_id' => 'required|exists:publishers,id',
            'translator_id' => 'nullable|exists:translators,id',
            'category_id' => 'required|exists:categories,id',
            'cover_type_id' => 'required|exists:cover_types,id',
            'genre_id' => 'required|exists:genres,id',
            'language_id' => 'required|exists:languages,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'required|string',
            'quantity' => 'required|integer|min:0',
            'original_price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'published_year' => 'required|integer',
            'number_pages' => 'required|integer',
            'length' => 'required|numeric|min:0',
            'width' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'weight' => 'required|numeric|min:0',
            'status' => 'required|in:instock,out_of_stock,pre_order',
            'authors' => 'required|array',
            'authors.*' => 'exists:authors,id', // Kiểm tra danh sách tác giả
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
    
        $book->update($request->except('image', 'images', 'authors'));
    
        // Cập nhật danh sách tác giả
        $book->authors()->sync($request->authors);
    
        if ($request->hasFile('images')) {
            foreach ($book->images as $img) {
                Storage::delete('public/book_images/' . $img->path);
            }
            $book->images()->delete();
    
            foreach ($request->file('images') as $images) {
                $path = $images->storeAs('book_images', $images->hashName(), 'public');
                $book->images()->create(['path' => basename($path)]);
            }
        }
    
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
