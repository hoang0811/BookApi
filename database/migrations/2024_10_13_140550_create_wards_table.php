<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWardsTable extends Migration
{
    public function up()
    {
        Schema::create('wards', function (Blueprint $table) {
            $table->string('id', 5)->primary(); // Khóa chính kiểu varchar
            $table->string('name', 50);
            $table->string('type', 50);
            $table->string('district_id', 5); // Mã quận/huyện liên kết

            $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wards');
    }
}
