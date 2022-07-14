<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSmsTemplateTableUpdateTitleOrder extends Migration
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
                if(!empty($value) && $value->name == 'OA Sent'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'OA Sent', 'id'=>$value->id])
                    ->update(["show_title_order" => 1]);
                }else if(!empty($value) && $value->name == 'OA Reminder (VIC)'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'OA Reminder (VIC)', 'id'=>$value->id])
                    ->update(["show_title_order" => 2]);
                }else if(!empty($value) && $value->name == 'OA Reminder (QLD)'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'OA Reminder (QLD)', 'id'=>$value->id])
                    ->update(["show_title_order" => 3]);
                }else if(!empty($value) && $value->name == 'OA Reminder (NSW)'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'OA Reminder (NSW)', 'id'=>$value->id])
                    ->update(["show_title_order" => 4]);
                }else if(!empty($value) && $value->name == 'OA Received'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'OA Received', 'id'=>$value->id])
                    ->update(["show_title_order" => 5]);
                }else if(!empty($value) && $value->name == 'Missed You'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'Missed You', 'id'=>$value->id])
                    ->update(["show_title_order" => 6]);
                }else if(!empty($value) && $value->name == 'Document Reminder'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'Document Reminder', 'id'=>$value->id])
                    ->update(["show_title_order" => 7]);
                }else if(!empty($value) && $value->name == 'GI Reminder'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'GI Reminder', 'id'=>$value->id])
                    ->update(["show_title_order" => 8]);
                }else if(!empty($value) && $value->name == 'CAB Reminder'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'CAB Reminder', 'id'=>$value->id])
                    ->update(["show_title_order" => 9]);
                }else if(!empty($value) && $value->name == 'CAB Documents'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'CAB Documents', 'id'=>$value->id])
                    ->update(["show_title_order" => 10]);
                }else if(!empty($value) && $value->name == 'Job Ready'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'Job Ready', 'id'=>$value->id])
                    ->update(["show_title_order" => 11]);
                }else if(!empty($value) && $value->name == 'Almost Active (CYF)'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'Almost Active (CYF)', 'id'=>$value->id])
                    ->update(["show_title_order" => 12]);
                }else if(!empty($value) && $value->name == 'Now Active (CYF)'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'Now Active (CYF)', 'id'=>$value->id])
                    ->update(["show_title_order" => 13]);
                }else if(!empty($value) && $value->name == 'Almost Active (Disability)'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'Almost Active (Disability)', 'id'=>$value->id])
                    ->update(["show_title_order" => 14]);
                }else if(!empty($value) && $value->name == 'Now Active (Disability)'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'Now Active (Disability)', 'id'=>$value->id])
                    ->update(["show_title_order" => 15]);
                }else if(!empty($value) && $value->name == 'Almost Active (CSS only)'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'Almost Active (CSS only)', 'id'=>$value->id])
                    ->update(["show_title_order" => 16]);
                }else if(!empty($value) && $value->name == 'Now Active (CSS only)'){
                    DB::table('tbl_sms_template')
                    ->where(["name" => 'Now Active (CSS only)', 'id'=>$value->id])
                    ->update(["show_title_order" => 17]);
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
