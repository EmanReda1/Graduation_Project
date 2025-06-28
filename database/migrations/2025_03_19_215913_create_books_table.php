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
            $table->id('book_id');
            $table->string('book_name');
            $table->string('author');
            $table->string('isbn_no');
            $table->integer('book_no');
            $table->decimal('price');
            $table->string('source');
            $table->text('summary');
            $table->string('department');
            $table->string('status');
            $table->string('place');
            $table->string('shelf_no');
            $table->integer('size');
            $table->date('release_date');
            $table->date('library_date');
            $table->string('image')->nullable();
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
