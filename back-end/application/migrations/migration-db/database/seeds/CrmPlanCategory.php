<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class CrmPlanCategory extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_crm_plan_category.json");
        $queryData = (array) json_decode($json, true);
        DB::table('tbl_crm_plan_category')->truncate();
        foreach ($queryData as $obj) {
            DB::table('tbl_crm_plan_category')->updateOrInsert(['id' => $obj['id']], $obj);
        }
    }

}
