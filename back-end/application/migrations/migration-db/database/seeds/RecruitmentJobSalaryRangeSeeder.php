<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class RecruitmentJobSalaryRangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	//echo Config::get('constants.JSON_FILE_PATH') . "tbl_recruitment_job_salary_range.json";die;
    	$json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_recruitment_job_salary_range.json");
    	$queryData = (array) json_decode($json, true);
    	//pr($queryData);
    	foreach ($queryData as $obj) {
    		DB::table('tbl_recruitment_job_salary_range')->updateOrInsert(['id' => $obj['id']], $obj);
    	}
    }
}
