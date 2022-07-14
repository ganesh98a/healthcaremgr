<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblMemberRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_member_role', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name',255);
            $table->text('description')->nullable()->comment('role description');           
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->unsignedInteger('archive')->default(0)->nullable()->comment('1 - Yes / 0 - No');
            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('tbl_member_role');
    }
}
