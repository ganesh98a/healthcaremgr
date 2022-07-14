<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantAppliedApplication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Let's attach tbl_person to tbl_recruitment_applicant
        Schema::table('tbl_recruitment_applicant', function(Blueprint $table) {
            if ( ! Schema::hasColumn('tbl_recruitment_applicant', 'person_id')) {
                // Let's use default 0 to account for existing applicants
                // Existing applicants don't necessarily have records in tbl_person
                $table->unsignedBigInteger('person_id')->nullable()->comment('tbl_person.id');
                $table->foreign('person_id')->references('id')->on('tbl_person')->onDelete(DB::raw('SET NULL'));
            }
        });

        // Let's move some of tbl_recruitment_applicant table to tbl_recruitment_applicant_applied_application
        // We're deprecating the original columns, we'll eventually remove those from tbl_recruitment_applicant
        // The reason we're moving these columns is because these columns should belong to applications and should not be applicant-specific
        Schema::table('tbl_recruitment_applicant_applied_application', function(Blueprint $table) {
            // Created this column because we're moving tbl_recruitment_applicant.recruiter to this column
            if ( ! Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'recruiter')) {
                $table->unsignedBigInteger('recruiter')->nullable()->comment('Recruiter'); 
            }
            
            // Created this column because we're moving jobId from tbl_recruitment_applicant.jobId to this column
            if ( ! Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'jobId')) {
                $table->unsignedBigInteger('jobId')->nullable()->comment('Job ID'); 
            }

            // Based on tbl_recruitment_applicant.status
            if ( ! Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'application_status') ) {
                $table->unsignedSmallInteger('application_status')->default(0)->comment('applied=0 in_progress=1 hired=2 rejected=3'); 
            }

            // current_stage does not belong to tbl_recruitment_applicant.current_stage
            // so we're adding current_stage here (we'll deprecate the tbl_recruitment_applicant.current_stage)
            if ( ! Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'current_stage') ) {
                $table->unsignedInteger('current_stage')->comment('primary key of tbl_recruitment_stage'); 
            }

            // channelId does not belong to tbl_recruitment_applicant.channelId
            // so we're adding channelId here (we'll deprecate the tbl_recruitment_applicant.channelId)
            if ( ! Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'channelId') ) {
                $table->unsignedInteger('channelId')->nullable()->comment('applicant apply job for which plat form'); 
            }

            // referrer_url is actually in tbl_recruitment_applicant_applied_application table


        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Use reverse of the order when we add/modify tables/cols
        
        Schema::table('tbl_recruitment_applicant_applied_application', function(Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'recruiter')) {
                $table->dropColumn('recruiter');
            }

            if (Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'jobId')) {
                $table->dropColumn('jobId');
            }

            if (Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'application_status') ) {
                $table->dropColumn('application_status');
            }

            if (Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'current_stage') ) {
                $table->dropColumn('current_stage');
            }

            if (Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'channelId') ) {
                $table->dropColumn('channelId');
            }
        });

        Schema::table('tbl_recruitment_applicant', function(Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant', 'person_id')) {
                $table->dropForeign(['person_id']);
                $table->dropColumn('person_id');
            }
        });
    }
}
