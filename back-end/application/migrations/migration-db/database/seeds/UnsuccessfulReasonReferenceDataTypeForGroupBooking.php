<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class UnsuccessfulReasonReferenceDataTypeForGroupBooking extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_reference_data_type_group_booking_unsuccessful_reason.json");
        $queryData = (array) json_decode($json, true);
        foreach ($queryData as $obj) {
            DB::table('tbl_reference_data_type')->updateOrInsert($obj);
        }
    }
}
