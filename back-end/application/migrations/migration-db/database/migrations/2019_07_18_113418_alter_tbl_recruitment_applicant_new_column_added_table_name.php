<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantNewColumnAddedTableName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_applicant')) {
            Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_applicant','applicant_code')) {
                    $table->renameColumn('applicant_code','appId');
                }
                if (Schema::hasColumn('tbl_recruitment_applicant','lastupdate')) {
                    $table->renameColumn('lastupdate','updated')->comment('applicant auto assign unique id like APPXXXXXX');
                }
                
                if (Schema::hasColumn('tbl_recruitment_applicant','application_category')) {
                    $table->dropColumn('application_category');
                }
                if (Schema::hasColumn('tbl_recruitment_applicant','applicant_classification')) {
                    $table->dropColumn('applicant_classification');
                }
                if (Schema::hasColumn('tbl_recruitment_applicant','job_exeperiance')) {
                    $table->dropColumn('job_exeperiance');
                }
                
                if (Schema::hasColumn('tbl_recruitment_applicant','currunt_stage')) {
                    $table->dropColumn('currunt_stage');
                }


                if (!Schema::hasColumn('tbl_recruitment_applicant','recruiter')) {
                    $table->unsignedInteger('recruiter')->nullable()->comment('recruiter assign to this applicant');
                }

                if (!Schema::hasColumn('tbl_recruitment_applicant','dublicateId')) {
                    $table->unsignedInteger('dublicateId')->nullable()->comment('applicant duplicate id');
                }

                if (!Schema::hasColumn('tbl_recruitment_applicant','dublicate_status')) {
                    $table->unsignedInteger('dublicate_status')->nullable()->comment('applicant duplicate id');
                }

                if (!Schema::hasColumn('tbl_recruitment_applicant','flagged_status')) {
                    $table->unsignedTinyInteger('flagged_status')->default(0)->comment('applicant mark as flagged 0 for no and 1 for yes ');
                }

                if (!Schema::hasColumn('tbl_recruitment_applicant','jobId')) {
                    $table->unsignedInteger('jobId')->default(0)->comment('auto increment id of tbl_recruitment_job');
                }

                if (!Schema::hasColumn('tbl_recruitment_applicant','channelId')) {
                    $table->unsignedInteger('channelId')->nullable()->comment('applicant apply job for which plat form');
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
        if (Schema::hasTable('tbl_recruitment_applicant')) {
            Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
            
            if (!Schema::hasColumn('tbl_recruitment_applicant','application_category')) {
                $table->unsignedTinyInteger('application_category');
            }

            if (!Schema::hasColumn('tbl_recruitment_applicant','applicant_classification')) {
                $table->unsignedTinyInteger('applicant_classification');
            }

            if (!Schema::hasColumn('tbl_recruitment_applicant','job_exeperiance')) {
                $table->unsignedTinyInteger('job_exeperiance');
            }

            if (Schema::hasColumn('tbl_recruitment_applicant','updated')) {
                $table->renameColumn('updated','lastupdate');
            }

            if (Schema::hasColumn('tbl_recruitment_applicant','appId')) {
                $table->renameColumn('appId','applicant_code');
            }



            if (Schema::hasColumn('tbl_recruitment_applicant','recruiter')) {
                $table->dropColumn('recruiter');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant','dublicateId')) {
                $table->dropColumn('dublicateId');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant','dublicate_status')) {
                $table->dropColumn('dublicate_status');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant','flagged_status')) {
                $table->dropColumn('flagged_status');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant','jobId')) {
                $table->dropColumn('jobId');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant','channelId')) {
                $table->dropColumn('channelId');
            }
          
            });
        }
    }
}
