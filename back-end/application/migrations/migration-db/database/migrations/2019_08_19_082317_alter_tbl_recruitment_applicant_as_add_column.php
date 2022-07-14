<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentApplicantAsAddColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
           if (!Schema::hasColumn('tbl_recruitment_applicant','dob')) {
              $table->date('dob')->default('0000-00-00');
            }
            $table->unsignedInteger('status')->comment('1- In Progress 2- Rejected 3- Completed ')->change();
      });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      if(Schema::hasTable('tbl_recruitment_applicant') && Schema::hasColumn('tbl_recruitment_applicant', 'dob') ) {
        Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
            $table->dropColumn('dob');
        });
        $table->unsignedInteger('status')->comment('1- Active, 0- Inactive')->change();
    }
}
}
