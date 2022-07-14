<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class ReferenceJobRequiredDocs extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {    
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_references_job_required_docs.json");
        $queryData = (array) json_decode($json, true);
        foreach ($queryData as $obj) {
            DB::table('tbl_references')->updateOrInsert(['type' => $obj['type'],'display_name' => $obj['display_name']], $obj);
        }    
    
    }
}
