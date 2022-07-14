<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentInterviewHistoryFeed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('tbl_recruitment_interview_history_feed')){
            Schema::create('tbl_recruitment_interview_history_feed', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('history_id')->unsigned()->comment('tbl_recruitment_interview_history.id');
                $table->foreign('history_id')->references('id')->on('tbl_recruitment_interview_history')->onDelete('cascade');
                $table->text('desc', 255)->nullable()->comment('Description of feed');
                $table->unsignedInteger('archive')->default(0)->nullable()->comment('1 - Yes / 0 - No');
                $table->unsignedInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
                $table->timestamps();                
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
        //
    }
}
