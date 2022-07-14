<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentInterviewTable extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_interview')) 
        {
            Schema::create('tbl_recruitment_interview', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title',100);
                
                $table->unsignedInteger('owner')->nullable()->comment('reference of tbl_member.id');

                $table->datetime('interview_start_datetime');
                $table->datetime('interview_end_datetime');
                
                $table->unsignedInteger('location_id')->nullable()->comment('tbl_recruitment_location');
                $table->unsignedInteger('interview_type_id')->nullable()->comment('tbl_recruitment_interview_type');

                $table->longText('description')->nullable();
                $table->unsignedTinyInteger('archive')->default(0)->comment('0- No, 1- Yes');
                
                $table->unsignedInteger('created_by')->nullable()->comment('reference of tbl_member.id');
                $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
                
                $table->unsignedInteger('updated_by')->nullable()->comment('reference of tbl_member.id');
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
                
                $table->dateTime('created')->nullable();
                $table->dateTime('updated')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_interview');
    }
}
