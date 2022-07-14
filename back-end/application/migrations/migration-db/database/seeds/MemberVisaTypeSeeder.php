<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class MemberVisaTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {       

        # visa type
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_member_visa_type.json");
        $queryData = (array) json_decode($json, true);
        foreach ($queryData as $obj) {
            DB::table('tbl_member_visa_type')->updateOrInsert($obj);
        }
    }
}
