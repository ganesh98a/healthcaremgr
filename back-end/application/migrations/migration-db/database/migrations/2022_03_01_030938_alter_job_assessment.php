<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterJobAssessment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_job_assessment', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_job_assessment', 'application_id')) {
                $table->unsignedInteger('application_id')->nullable()->after('applicant_id')->comment('tbl_recruitment_applicant_applied_application.id'); 
                $table->foreign('application_id', 'application_foreign')->references('id')->on('tbl_recruitment_applicant_applied_application');              
            }
            
            if (!Schema::hasColumn('tbl_recruitment_job_assessment', 'template_id')) {
                $table->unsignedInteger('template_id')->nullable()->after('uuid')->comment('tbl_recruitment_oa_template.id'); 
                $table->foreign('template_id', 'template_foreign')->references('id')->on('tbl_recruitment_oa_template');              
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasTable('tbl_recruitment_job_assessment') && Schema::hasColumn('tbl_recruitment_job_assessment', 'application_id') && Schema::hasColumn('tbl_recruitment_job_assessment', 'template_id')) {
            Schema::table('tbl_recruitment_job_assessment', function (Blueprint $table) {
               
                $table->dropColumn('application_id');
                $table->dropColumn('template_id');
            });
            
          }
    }
}
