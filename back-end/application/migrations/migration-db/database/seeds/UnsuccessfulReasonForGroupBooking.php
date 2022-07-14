<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class UnsuccessfulReasonForGroupBooking extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {    
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_references_unsuccessful_group_booking_reason.json");
        $queryData = (array) json_decode($json, true);
        $res = DB::select("SELECT id FROM tbl_reference_data_type WHERE key_name = 'unsuccessful_group_booking_reason'");
        if (isset($res) == true && empty($res) == false) {
            $type_id = $res[0]->id;
            foreach ($queryData as $obj) {
                $temp = $obj;
                $temp['type'] = $type_id;
                DB::table('tbl_references')->insert($temp);
            }
        }
        
    }   
}
