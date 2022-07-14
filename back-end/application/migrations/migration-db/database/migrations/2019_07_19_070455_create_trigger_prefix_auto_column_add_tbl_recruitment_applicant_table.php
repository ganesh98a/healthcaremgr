<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerPrefixAutoColumnAddTblRecruitmentApplicantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_recruitment_applicant_befor_insert_add_appid`');
        DB::unprepared("CREATE TRIGGER `tbl_recruitment_applicant_befor_insert_add_appid` BEFORE INSERT ON `tbl_recruitment_applicant` FOR EACH ROW
        IF NEW.appId IS NULL or NEW.appId='' THEN
          SET NEW.appId = (SELECT CONCAT('APP',((SELECT id FROM tbl_recruitment_applicant ORDER BY id DESC LIMIT 1) + 1)+10000));
          END IF;"); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_recruitment_applicant_befor_insert_add_appid`');
    }
}
