<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class PlanDisputeContactMethod extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = File::get(Config::get('constants.JSON_FILE_PATH')."tbl_plan_dispute_contact_method.json");
        $queryData = (array) json_decode($json, true);
        DB::statement("TRUNCATE TABLE tbl_plan_dispute_contact_method");
        foreach ($queryData as $obj) {
            DB::table('tbl_plan_dispute_contact_method')->updateOrInsert(['id' => $obj['id']],$obj);
        }
    }
}
