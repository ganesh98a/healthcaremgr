<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentJobAssessmentAddDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_job_assessment', function (Blueprint $table) {
            $table->datetime('start_date_time')->nullable()->comment('assessment started date time')->after('uuid');
            $table->datetime('completed_date_time')->nullable()->comment('assessment completed date time')->after('start_date_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_recruitment_job_assessment')) {
            Schema::table('tbl_recruitment_job_assessment', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_job_assessment','start_date_time')) {
                    $table->dropColumn('start_date_time');
                }
                if (Schema::hasColumn('tbl_recruitment_job_assessment','completed_date_time')) {
                    $table->dropColumn('completed_date_time');
                }
            });
        }
        
    }
}
