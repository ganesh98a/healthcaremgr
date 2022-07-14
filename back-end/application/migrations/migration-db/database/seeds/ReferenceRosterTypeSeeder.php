<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class ReferenceRosterTypeSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        # Roster type seeder
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_reference_data_roster_type.json");
        $queryData = (array) json_decode($json, true);
        foreach ($queryData as $obj) {
            $temp_type = [];
            $temp_type['title'] = $obj['title'];
            $temp_type['key_name'] = $obj['key_name'];
            $temp_type['created'] = $obj['created'];
            $type_id = DB::table('tbl_reference_data_type')->insertGetId($temp_type);

            $data = $obj['data'];
            foreach ($data as $obj_data) {
                $temp = $obj_data;
                $temp['type'] = $type_id;
                DB::table('tbl_references')->insert($temp);
            }
        }

        # Roster funding type seeder
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_reference_data_roster_funding_type.json");
        $queryData = (array) json_decode($json, true);
        foreach ($queryData as $obj) {
            $temp_type = [];
            $temp_type['title'] = $obj['title'];
            $temp_type['key_name'] = $obj['key_name'];
            $temp_type['created'] = $obj['created'];
            $type_id = DB::table('tbl_reference_data_type')->insertGetId($temp_type);

            $data = $obj['data'];
            foreach ($data as $obj_data) {
                $temp = $obj_data;
                $temp['type'] = $type_id;
                DB::table('tbl_references')->insert($temp);
            }
        }
    }

}
