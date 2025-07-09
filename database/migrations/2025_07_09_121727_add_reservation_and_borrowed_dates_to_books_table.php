<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("books", function (Blueprint $table) {
            $table->timestamp("reservation_date")->nullable()->after("status");
            $table->timestamp("borrowed_date")->nullable()->after("reservation_date");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("books", function (Blueprint $table) {
            $table->dropColumn("reservation_date");
            $table->dropColumn("borrowed_date");
        });
    }
};
