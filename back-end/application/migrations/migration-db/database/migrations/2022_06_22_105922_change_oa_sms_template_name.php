<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeOaSmsTemplateName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $items = DB::select("SELECT * FROM `tbl_sms_template`");
        
        if(!empty($items)){
            foreach($items as $value) {
                if(!empty($value) && $value->name == 'OA reminder (VIC)'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'OA reminder (VIC)', 'id'=>$value->id])
                    ->update(["name" => 'OA Reminder (VIC)', "short_description"=>"OA Reminder (VIC)"]);
                }
                if(!empty($value) && $value->name == 'OA reminder (QLD)'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'OA reminder (QLD)', 'id'=>$value->id])
                    ->update(["name" => 'OA Reminder (QLD)', "short_description"=>"OA Reminder (QLD)"]);
                }
                if(!empty($value) && $value->name == 'OA reminder (NSW)'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'OA reminder (NSW)', 'id'=>$value->id])
                    ->update(["name" => 'OA Reminder (NSW)', "short_description"=>"OA Reminder (NSW)"]);
                }
                if(!empty($value) && $value->name == 'OA received'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'OA received', 'id'=>$value->id])
                    ->update(["name" => 'OA Received', "short_description"=>"OA Received"]);
                }
                if(!empty($value) && $value->name == 'Online Assessment Initiated'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'Online Assessment Initiated', "used_to_initiate_oa" => 1, 'archive'=>0])
                    ->update(["used_to_initiate_oa" => 0, 'archive'=>1]);
                }
                if(!empty($value) && $value->name == 'OA Sent'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'OA Sent', "used_to_initiate_oa" => 0, 'archive'=>0])
                    ->update(["used_to_initiate_oa" => 1, "created_by" => 1]);
                }
            }
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
