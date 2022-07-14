<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

// DANGER: DO NOT re-run this seeder if the user has 
// modified one of the names of account contact roles or else you'll risk creating duplicate data
class ReferencesAccountContactRole extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_references_account_contact_role_type.json");
        $queryData = (array) json_decode($json, true);
        foreach ($queryData as $obj) {
            DB::table('tbl_references')->updateOrInsert(['type' => $obj['type'],'display_name' => $obj['display_name']], $obj);
        }    
    
    }
}
