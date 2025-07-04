<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('exams', function (Blueprint $table) {
        $table->string('pdf')->nullable()->change();  // تعديل العمود
    });
}

public function down()
{
    Schema::table('exams', function (Blueprint $table) {
        $table->string('pdf')->nullable(false)->change(); // rollback للتعديل
    });
}
};
