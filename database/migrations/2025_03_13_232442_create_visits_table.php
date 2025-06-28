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
        Schema::create('visits', function (Blueprint $table) {
            $table->id('visit_id');
            $table->foreignId('student_id')->constrained('students', 'student_id')->onDelete('cascade');
           // $table->$table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade'); // if student deleted all visits delelted
            $table->timestamp('visit_time')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
