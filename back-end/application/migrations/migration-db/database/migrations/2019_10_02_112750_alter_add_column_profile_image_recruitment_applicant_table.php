<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnProfileImageRecruitmentApplicantTable extends Migration
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
            $table->string('profile_image',150)->after('appId');
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
       if (Schema::hasTable('tbl_recruitment_applicant') && Schema::hasColumn('tbl_recruitment_applicant', 'profile_image')) {
         Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
            $table->dropColumn('profile_image');
        });
      }
    }
}
