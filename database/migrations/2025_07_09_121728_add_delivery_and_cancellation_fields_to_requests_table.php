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
        Schema::table("requests", function (Blueprint $table) {
            $table->timestamp("delivered_at")->nullable()->after("type");
            $table->unsignedBigInteger("delivered_by")->nullable()->after("delivered_at");
            $table->timestamp("returned_at")->nullable()->after("delivered_by");
            $table->unsignedBigInteger("returned_to")->nullable()->after("returned_at");
            $table->timestamp("cancelled_at")->nullable()->after("returned_to");
            $table->string("cancellation_reason")->nullable()->after("cancelled_at");

            // إضافة مفاتيح أجنبية إذا كان لديكِ جدول users للمشرفين
            // تأكدي من أن جدول users موجود وأن العمود الذي تشيرين إليه هو 'id'
            // $table->foreign("delivered_by")->references("id")->on("users")->onDelete("set null");
            // $table->foreign("returned_to")->references("id")->on("users")->onDelete("set null");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("requests", function (Blueprint $table) {
            // يجب حذف المفاتيح الأجنبية أولاً قبل حذف الأعمدة
            // $table->dropForeign(["delivered_by"]);
            // $table->dropForeign(["returned_to"]);
            $table->dropColumn([
                "delivered_at",
                "delivered_by",
                "returned_at",
                "returned_to",
                "cancelled_at",
                "cancellation_reason",
            ]);
        });
    }
};
