<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class FMSFeedBackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "feed_category.json");
        $queryData = (array) json_decode($json, true);

        $res = DB::select("SELECT id from tbl_reference_data_type WHERE key_name ='fms_feed_category'");

        if(!empty($res)) {

            DB::statement("DELETE FROM tbl_references WHERE key_name IN('grievance','staff_performance','serious_misconduct', 'investigation', 'cat_1_investigation'
            , 'own_alert', 'client_mismatch','client_survey_response', 'now_works_for_organisation', 'cat_1_incident')");

            foreach ($queryData['fms_feed_category'] as $obj) {

                DB::table('tbl_references')->insert([
                    'type' => $res[0]->id,
                    'display_name' => $obj['name'],
                    'key_name' => $obj['key_name'],
                    'created' => date('Y-m-d h:i:s'),
                    'updated' => date('Y-m-d h:i:s'),
                    'archive' => 0
                ]);
            }
        }

    }
}
