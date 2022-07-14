<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantAsAlterColumnDefaultValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_applicant')) 
        {
            Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
                $table->unsignedInteger('duplicatedId')->default('0')->change();
                $table->unsignedSmallInteger('duplicated_status')->default('0')->change();
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
        if (Schema::hasTable('tbl_recruitment_applicant')) 
        {
            Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
                $table->unsignedInteger('duplicatedId')->nullable()->change();
                $table->unsignedSmallInteger('duplicated_status')->nullable()->change();
            });
        }
    }
}
