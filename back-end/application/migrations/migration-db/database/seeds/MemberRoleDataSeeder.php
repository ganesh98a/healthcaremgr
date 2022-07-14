<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class MemberRoleDataSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_member_role.json");
        $queryData = (array) json_decode($json, true);

        $Update_res = DB::select("UPDATE tbl_member_role SET archive = 1 WHERE 1");

        foreach ($queryData as $obj) {
            $temp = $obj;
            $temp['start_date'] = date('Y-m-d h:i:s');
            DB::table('tbl_member_role')->insert($temp);
        }

        // update the service type
        $Update_res = DB::select("UPDATE tbl_reference_data_type SET archive = 1 WHERE key_name = 'organisation_service_type'");
        $res = DB::select("SELECT id FROM tbl_reference_data_type WHERE key_name = 'organisation_service_type'");
        if (isset($res) == true && empty($res) == false) {
            $type_id = $res[0]->id;
            DB::statement("UPDATE tbl_references SET archive = 1 WHERE type = '".$type_id."'");
        }
        
    }

}
