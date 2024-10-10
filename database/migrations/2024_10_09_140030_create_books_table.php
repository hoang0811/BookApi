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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('isbn')->nullable()->unique();
            $table->foreignId('publisher_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('co_publisher_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('translator_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('author_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('cover_type_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('genre_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('language_id')->nullable()->constrained()->onDelete('set null');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->string('internal_code')->nullable();
            $table->year('published_year')->nullable();
            $table->date('published_date')->nullable();
            $table->integer('number_pages')->nullable();
            $table->string('size')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->text('keywords')->nullable();
            $table->enum('status', ['instock', 'out_of_stock', 'pre_order'])->default('instock');
        
            $table->timestamps();
        });
        
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};