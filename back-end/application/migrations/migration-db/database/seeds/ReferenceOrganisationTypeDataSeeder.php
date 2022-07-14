<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class ReferenceOrganisationTypeDataSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_reference_data_org_type.json");
        $queryData = (array) json_decode($json, true);
        $res = DB::select("SELECT id FROM tbl_reference_data_type WHERE key_name = 'organisation_type'");
        if (isset($res) == true && empty($res) == false) {
            $type_id = $res[0]->id;
            DB::statement("UPDATE tbl_references SET archive = 1 WHERE type = '".$type_id."'");
            foreach ($queryData as $obj) {
                $temp = $obj;
                $temp['type'] = $type_id;
                DB::table('tbl_references')->insert($temp);
            }
        }
        
    }

}
