<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblShiftSkills extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_shift_skills', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('shift_id')->comment('primary key of tbl_shift');
            $table->unsignedInteger('skill_id')->comment('primary key of tbl_references id');
            $table->unsignedInteger('condition')->comment('1-mandatory 2-optional');
            $table->unsignedSmallInteger('archive')->comment('0 - Not/1 - Yes')->default(0);
            $table->dateTime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->dateTime('updated')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_shift_skills');
    }
}
