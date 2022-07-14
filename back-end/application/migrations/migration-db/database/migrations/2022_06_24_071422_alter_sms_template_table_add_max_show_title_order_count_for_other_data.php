<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSmsTemplateTableAddMaxShowTitleOrderCountForOtherData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $items = DB::select("SELECT * FROM `tbl_sms_template` where show_title_order=0");
        
        
        if(!empty($items)){
            foreach($items as $value) {
                $order_value = DB::select("SELECT MAX(show_title_order) as order_no FROM `tbl_sms_template`");
                $title_order = $order_value[0]->order_no+1;
                if(!empty($value)){
                    DB::table('tbl_sms_template')
                    ->where(['id'=>$value->id])
                    ->update(["show_title_order" => $title_order]);
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
