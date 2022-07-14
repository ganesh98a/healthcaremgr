<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTblRecruitmentApplicantAppliedApplicationAddNewColumnFormApplicant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_applicant_applied_application')) {
            Schema::table('tbl_recruitment_applicant_applied_application', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_recruitment_applicant_applied_application','from_applicant_id')){
                    $table->unsignedInteger('from_applicant_id')->default(0)->comment('auto increment id of tbl_recruitment_applicant table .it come when admin add dupplicate application position to current application position form this applicant id');
                }
                if(!Schema::hasColumn('tbl_recruitment_applicant_applied_application','from_status')){
                    $table->unsignedSmallInteger('from_status')->default(0)->comment('0-direct applied form channel,1- added from duplicate application to current application');
                }
           
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
        if (Schema::hasTable('tbl_recruitment_applicant_applied_application')) {
            Schema::table('tbl_recruitment_applicant_applied_application', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_recruitment_applicant_applied_application','from_applicant_id')){
                    $table->dropColumn('from_applicant_id');
                }
                if(Schema::hasColumn('tbl_recruitment_applicant_applied_application','from_status')){
                    $table->dropColumn('from_status');
                }
            });
        }
    }
}
