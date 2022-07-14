<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class SkillSetListFromOcpSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $type_id = DB::select("select id from tbl_reference_data_type WHERE key_name = 'skill'");
        echo $type_id[0]->id;
        echo "\n";
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_reference_skills_list_from_ocp.json");
        $queryData = (array) json_decode($json, true);
        foreach ($queryData as $obj) {
            $obj['type'] = $type_id[0]->id;
            DB::table('tbl_references')->updateOrInsert($obj);
        }
    }

        /**
     * Reverse the database seeds.
     *
     * @return void
     */
    public function rollback() {
        DB::delete("delete from tbl_references where source='from_ocp'");
    }

}
