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
        Schema::create('triggers', function (Blueprint $table) {
            $table->id('log_id');
            $table->string('event_type'); // insert, update, delete
            $table->string('table_name');
            $table->integer('record_id'); // affected record
            $table->foreignId('student_id')->nullable()->constrained('students', 'student_id')->onDelete('cascade');
            $table->foreignId('librarian_id')->nullable()->constrained('librarian', 'librarian_id')->onDelete('cascade');
            $table->timestamp('event_time')->useCurrent();
            $table->text('description')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('triggers');
    }
};
