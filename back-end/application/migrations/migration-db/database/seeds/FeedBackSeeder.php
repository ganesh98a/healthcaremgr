<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class FeedBackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "feed_back.json");
        $queryData = (array) json_decode($json, true);

        $data_type = ['fms_feed_category' => 'FMS feed category', 'fms_initiator_category' => 'FMS initiator category',
            'fms_against_category' => 'FMS against Category', 'fms_department_details' => 'FMS department details'];


        foreach($data_type as $key => $data) {

            DB::table('tbl_reference_data_type')->insert([
                'title' => $data,
                'key_name' => $key,
                'created' => date('Y-m-d h:i:s'),
                'archive' => 0,
            ]);

            $ref_id = DB::getPdo()->lastInsertId();

            foreach ($queryData[$key] as $obj) {

                DB::table('tbl_references')->insert([
                    'type' => $ref_id,
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
