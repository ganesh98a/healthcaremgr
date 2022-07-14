<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * migration file to create the tbl_recruitment_form_applicant table
 * the table is used to store details of any screening interview completed by the recruiter
 * for any applicant and its application
 */
class CreateTblRecruitmentFormApplicantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_recruitment_form_applicant', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('applicant_id')->comment('tbl_recruitment_applicant.id');
            $table->unsignedInteger('application_id')->comment('tbl_recruitment_applicant_applied_application.id');
            $table->unsignedInteger('form_id')->comment('tbl_recruitment_form.id');
            $table->unsignedInteger('completed_by')->comment('tbl_member.id');
            $table->timestamp('date_created', 0)->nullable();
            $table->timestamp('date_updated', 0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_recruitment_form_applicant');
    }
}
