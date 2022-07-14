<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblShiftSupportDuration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_shift_ndis_support_duration', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('shift_id')->comment('tbl_shift.id');
            $table->foreign('shift_id')->references('id')->on('tbl_shift');
            $table->unsignedTinyInteger('category')->comment('1 = scheduled / 2 = actual');
            $table->unsignedTinyInteger('support_type')->comment('1 - Self Care / 2 - Comm Access');
            $table->time('duration')->default('00:00:00')->comment('hh:mm:ss');
            $table->unsignedTinyInteger('order')->comment('Asc Order');
            $table->unsignedSmallInteger('archive')->default(0)->comment('0 -Not/1- Yes');
            $table->unsignedInteger('created_by')->nullable()->comment('priamry key tbl_users');
            $table->foreign('created_by')->references('id')->on('tbl_users')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('updated_by')->nullable()->comment('priamry key tbl_users');
            $table->foreign('updated_by')->references('id')->on('tbl_users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_shift_ndis_support_duration');
    }
}
