<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id(); // Khóa chính tự động tăng
            $table->unsignedBigInteger('user_id'); // ID người dùng
            $table->string('name', 255)->notNull(); // Tên địa chỉ
            $table->string('phone', 20)->notNull(); // Số điện thoại
            $table->string('email', 150)->nullable(); // Email (có thể null)
            $table->string('district_id', 5)->notNull(); // ID quận
            $table->string('ward_id', 5)->notNull(); // ID phường
            $table->string('province_id', 5)->notNull(); // ID tỉnh
            $table->string('street', 200)->nullable(); // Đường (có thể null)
            $table->string('address_type', 50)->default('home'); // Loại địa chỉ
            $table->boolean('is_default')->default(0); // Địa chỉ mặc định
            $table->timestamps(); // Timestamps cho created_at và updated_at
            
            // Khóa ngoại
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
