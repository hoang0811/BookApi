<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProvincesTable extends Migration
{
    public function up()
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->string('id', 5)->primary(); // Tạo khóa chính là varchar(5)
            $table->string('name', 100);
            $table->string('type', 30);
            $table->string('slug', 30)->nullable(); // Mặc định là NULL
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('provinces');
    }
}
