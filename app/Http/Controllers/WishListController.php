<?php

namespace App\Http\Controllers;

use App\Models\WishList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishListController extends Controller
{
    // Lấy danh sách sản phẩm yêu thích của người dùng hiện tại
    public function index()
    {
        $userId = Auth()->id();
        $wishLists = WishList::with('book')
            ->where('user_id', $userId)
            ->get();

        return response()->json($wishLists);
    }

    // Thêm sản phẩm vào danh sách yêu thích
    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        $userId = auth()->id();
        $bookId = $request->book_id;

        // Kiểm tra xem sách đã có trong danh sách chưa
        $exists = WishList::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Book already in wishlist'], 400);
        }

        // Thêm sách vào danh sách yêu thích
        WishList::create([
            'user_id' => $userId,
            'book_id' => $bookId,
        ]);

        return response()->json(['message' => 'Book added to wishlist']);
    }

    // Xóa sản phẩm khỏi danh sách yêu thích
    public function destroy($id)
    {
        $userId = auth()->id();

        $wishList = WishList::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$wishList) {
            return response()->json(['message' => 'Wishlist item not found'], 404);
        }

        $wishList->delete();

        return response()->json(['message' => 'Book removed from wishlist']);
    }
}
