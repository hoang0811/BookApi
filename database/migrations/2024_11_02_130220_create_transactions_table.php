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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id(); // Mã giao dịch
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // Mã đơn hàng
            $table->timestamp('transaction_date')->useCurrent(); // Ngày giao dịch
            $table->decimal('amount', 10, 2); // Số tiền giao dịch
            $table->enum('payment_method', ['cod', 'momo', 'vnpay'])->default('cod'); // Phương thức thanh toán
            $table->enum('transaction_status', ['paid', 'refunded', 'pending', 'failed'])->default('pending'); // Trạng thái giao dịch
            $table->decimal('refund_amount', 10, 2)->default(0); // Số tiền hoàn lại nếu có
            $table->timestamps(); // Thời gian tạo và cập nhật giao dịch
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
