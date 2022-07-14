<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class OppourtunityLostReason extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $reference_id = DB::select("Select id from tbl_reference_data_type where key_name='cancel_and_lost_reason_opp'");

        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_reference_data_lost_reason_opportunity.json");
        $queryData = (array) json_decode($json, true);

        if(!empty($reference_id)){
            foreach ($queryData as $obj) {
                $obj['type']= $reference_id[0]->id;
                DB::table('tbl_references')->updateOrInsert(['type' => $reference_id[0]->id,'display_name' => $obj['display_name'],'key_name' => $obj['key_name']], $obj);
            }
        }
    }
}
