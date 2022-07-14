<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentJobPostedDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_job_posted_docs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('jobId');
            $table->unsignedInteger('requirement_docs_id')->comment('id of tbl_recruitment_job_requirement_docs');
            $table->unsignedInteger('is_required')->comment('1- required/ 0 - optional');
            $table->unsignedInteger('archive')->comment('0- not/ 1 - archive');
            $table->dateTime('created')->default('0000-00-00 00:00:00');
            $table->timestamp('updated')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_job_posted_docs');
    }
}
