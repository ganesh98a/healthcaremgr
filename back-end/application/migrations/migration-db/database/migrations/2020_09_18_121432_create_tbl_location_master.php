<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblLocationMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_locations_master', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name',255);
            $table->text('description',255);
            $table->unsignedInteger('participant_id')->comment('tbl_participants_master.id');
            $table->foreign('participant_id')->references('id')->on('tbl_participants_master')->onDelete('CASCADE');
            $table->unsignedInteger('active')->nullable()->comment('1 - Yes / 0 - No');
            $table->unsignedInteger('archive')->default(0)->nullable()->comment('1 - Yes / 0 - No');
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('tbl_locations_master');
    }
}
