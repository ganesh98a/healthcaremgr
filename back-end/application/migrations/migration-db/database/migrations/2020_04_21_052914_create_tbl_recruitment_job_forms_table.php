<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentJobFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_job_forms')) {
            Schema::create('tbl_recruitment_job_forms', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('Junction table between tbl_recruitment_job & tbl_recruitment_form. [job_id, form_id] must be unique');
                $table->unsignedInteger('job_id')->comment('tbl_recruitment_job.id');
                $table->unsignedBigInteger('form_id')->comment("tbl_recruitment_form.id; Usually ids of this column have category of 'Job questions' (interview_type=5)");
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
        Schema::dropIfExists('tbl_recruitment_job_forms');
    }
}
