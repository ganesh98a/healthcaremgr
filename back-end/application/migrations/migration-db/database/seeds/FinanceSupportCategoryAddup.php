<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class FinanceSupportCategoryAddup extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_finance_support_category_add_up.json");
        $queryData = (array) json_decode($json, true);
        foreach ($queryData as $obj) {
            DB::table('tbl_finance_support_category')->updateOrInsert($obj);
        }

        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_finance_support_registration_add_up.json");
        $queryData = (array) json_decode($json, true);
        foreach ($queryData as $obj) {
            DB::table('tbl_finance_support_registration_group')->updateOrInsert($obj);
        }
    }
}
