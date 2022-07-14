<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class RolePermissionSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_role_permission.json");
        $queryData = (array) json_decode($json, true);
        //DB::table('tbl_role_permission')->truncate();
        foreach ($queryData as $obj) {
            DB::table('tbl_role_permission')->updateOrInsert(['id' => $obj['id']], $obj);
        }
    }

}
