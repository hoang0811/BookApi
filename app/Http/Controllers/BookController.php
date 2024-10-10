<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookResource;
use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::orderBy('id', 'DESC')->paginate(10);
        return new BookResource(true, 'Data retrieved successfully', $books);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
