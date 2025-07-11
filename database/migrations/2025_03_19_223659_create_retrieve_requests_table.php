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
        Schema::create('retrieve_requests', function (Blueprint $table) {
            $table->id('retrieve_id');
            $table->foreignId('request_id')->constrained('requests', 'request_id')->onDelete('cascade');
            $table->timestamp('request_date')->useCurrent();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retrieve_requests');
    }
};
