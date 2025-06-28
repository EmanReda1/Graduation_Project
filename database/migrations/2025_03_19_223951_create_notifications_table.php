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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('notification_id');
            $table->foreignId('student_id')->nullable()->constrained('students', 'student_id')->onDelete('cascade');
            $table->foreignId('librarian_id')->nullable()->constrained('librarian', 'librarian_id')->onDelete('cascade');
            $table->string('type');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('date_time')->useCurrent();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
