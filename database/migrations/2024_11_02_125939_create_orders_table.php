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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // Mã đơn hàng
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Mã người dùng
            $table->string('name'); // Tên người nhận
            $table->string('phone'); // Số điện thoại
            $table->string('email')->nullable(); // Email (tuỳ chọn)
            $table->string('district'); // Quận
            $table->string('province'); // Tỉnh
            $table->string('ward'); // Phường
            $table->string('street');
            $table->timestamp('order_date')->useCurrent(); // Ngày đặt hàng
            $table->string('ghn')->default('null');
            $table->decimal('total_amount', 10, 2); // Tổng số tiền
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->decimal('total_discount', 10, 2)->default(0); // Tổng tiền giảm giá
            $table->foreignId('discount_id')->nullable()->constrained(); // Mã giảm giá (tuỳ chọn)
            $table->enum('order_status', ['ordered', 'delivered', 'canceled', 'rejected', 'returned'])->default('ordered'); // Trạng thái đơn hàng
            $table->timestamp('delivered_at')->nullable(); // Thời gian giao hàng
            $table->timestamp('canceled_at')->nullable(); // Thời gian hủy đơn
            $table->timestamps(); // Thời gian tạo và cập nhật
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
