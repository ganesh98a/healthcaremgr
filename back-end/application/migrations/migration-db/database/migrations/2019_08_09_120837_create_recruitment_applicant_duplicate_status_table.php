<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentApplicantDuplicateStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        if (!Schema::hasTable('tbl_recruitment_applicant_duplicate_status')) {
            Schema::create('tbl_recruitment_applicant_duplicate_status', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('applicant_id')->comment('auto increment id of tbl_recruitment_applicant table.');
                $table->unsignedSmallInteger('status')->default('1')->comment('1-pending,2-accept,3-rejected');
                $table->unsignedSmallInteger('accept_sub_status')->default('0')->comment('0-nothing,1-add current application,2-modify exsisting application');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->dateTime('action_taken_date')->default('0000-00-00 00:00:00');
                $table->timestamp('lastupdate')->useCurrent();
                $table->unsignedTinyInteger('archive')->default('0');
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
        Schema::dropIfExists('tbl_recruitment_applicant_duplicate_status');
    }
}
