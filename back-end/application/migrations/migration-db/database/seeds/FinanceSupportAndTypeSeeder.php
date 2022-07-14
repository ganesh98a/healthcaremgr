<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class FinanceSupportAndTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        # Support type
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_finance_support_type.json");
        $queryData = (array) json_decode($json, true);
        foreach ($queryData as $obj) {
            DB::table('tbl_finance_support_type')->updateOrInsert($obj);
        }

        # Support Purpose
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_finance_support_purpose.json");
        $queryData = (array) json_decode($json, true);
        foreach ($queryData as $obj) {
            DB::table('tbl_finance_support_purpose')->updateOrInsert($obj);
        }
    }
}
