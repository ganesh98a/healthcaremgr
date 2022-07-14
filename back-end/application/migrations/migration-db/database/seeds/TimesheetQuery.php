<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class TimesheetQuery extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_reference_data_type.json");
        $queryData = (array) json_decode($json, true);
        foreach ($queryData as $obj) {
            DB::table('tbl_reference_data_type')->updateOrInsert(['id' => $obj['id']], $obj);
        }

        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_references_timesheet_query.json");
        $queryData = (array) json_decode($json, true);
        foreach ($queryData as $obj) {
            DB::table('tbl_references')->updateOrInsert(['type' => $obj['type'],'key_name' => $obj['key_name']], $obj);
        }
    }
}
