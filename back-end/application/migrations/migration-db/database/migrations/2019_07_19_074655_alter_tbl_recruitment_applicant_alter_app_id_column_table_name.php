<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Doctrine\TinyInteger;

class AlterTblRecruitmentApplicantAlterAppIdColumnTableName extends Migration
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
                if (Schema::hasColumn('tbl_recruitment_applicant','appId')) {
                    $table->string('appId',50)->nullable()->change();
                }
                if (Schema::hasColumn('tbl_recruitment_applicant','dublicate_status')) {
                    $table->renameColumn('dublicate_status','duplicated_status');
                }
                if (Schema::hasColumn('tbl_recruitment_applicant','dublicateId')) {
                    $table->renameColumn('dublicateId','duplicatedId');
                }
            });

            Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
                
                if (Schema::hasColumn('tbl_recruitment_applicant','duplicated_status')) {
                    $table->unsignedSmallInteger('duplicated_status')->nullable()->defualt(0)->comment('applicant mark as duplicated 0 for no and 1 for yes')->change();                   
                }
                if (Schema::hasColumn('tbl_recruitment_applicant','duplicatedId')) {
                   $table->unsignedInteger('duplicatedId')->nullable()->defualt(0)->comment('applicant duplicate id')->change();
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
                if (Schema::hasColumn('tbl_recruitment_applicant','appId')) {
                    $table->string('appId',30)->change();
                }
            });
        }
    }
}
