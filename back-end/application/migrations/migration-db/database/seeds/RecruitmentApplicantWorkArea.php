<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;


class RecruitmentApplicantWorkArea  extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    { 

    	$json = File::get(Config::get('constants.JSON_FILE_PATH')."tbl_recruitment_applicant_work_area.json");
    	$queryData = (array) json_decode($json, true);
        DB::table('tbl_recruitment_applicant_work_area')->truncate();
       foreach ($queryData as $obj) {
          DB::table('tbl_recruitment_applicant_work_area')->updateOrInsert(['id' => $obj['id']],
             $obj);
      }
  }
}
