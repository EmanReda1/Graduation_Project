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
        Schema::table("visits", function (Blueprint $table) {
            // تحقق مما إذا كان العمود موجودًا قبل إضافته
            if (!Schema::hasColumn("visits", "created_at")) {
                $table->timestamp("created_at")->nullable();
            }
            if (!Schema::hasColumn("visits", "updated_at")) {
                $table->timestamp("updated_at")->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("visits", function (Blueprint $table) {
            // تحقق مما إذا كان العمود موجودًا قبل حذفه
            if (Schema::hasColumn("visits", "created_at")) {
                $table->dropColumn("created_at");
            }
            if (Schema::hasColumn("visits", "updated_at")) {
                $table->dropColumn("updated_at");
            }
        });
    }
};
