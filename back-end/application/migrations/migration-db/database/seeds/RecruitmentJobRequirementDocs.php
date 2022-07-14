<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class RecruitmentJobRequirementDocs extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_recruitment_job_requirement_docs.json");
    	$queryData = (array) json_decode($json, true);
		
    	foreach ($queryData as $obj) {
    		DB::table('tbl_recruitment_job_requirement_docs')->updateOrInsert(['id' => $obj['id']], $obj);
    	}
    }
}
