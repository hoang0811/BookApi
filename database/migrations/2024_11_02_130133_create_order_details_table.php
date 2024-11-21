<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id(); // Mã chi tiết đơn hàng
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // Mã đơn hàng
            $table->foreignId('book_id')->constrained()->onDelete('cascade'); // Mã sách
            $table->integer('quantity'); // Số lượng sách
            $table->decimal('price', 10, 2); // Giá sách
            $table->string('option')->nullable(); // Tuỳ chọn (nếu có)
            $table->timestamps(); // Thời gian tạo và cập nhật
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
