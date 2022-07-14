<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateMealTimeDataToNutritionalSupport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $list_of_mealtime = DB::select("SELECT * FROM `tbl_need_assessment_mealtime` WHERE archive=0 ORDER BY `tbl_need_assessment_mealtime`.`id` ASC");
        
        foreach($list_of_mealtime as $value) { 
                $insData = [
                    'support_with_eating' => $value->mealtime_assistance_plan,
                    'support_desc' => $value->assistance_plan_requirement ?? '',
                    'risk_choking' => $value->risk_choking,
                    'risk_aspiration' => $value->risk_aspiration,
                    'need_assessment_id'=> $value->need_assessment_id,                
                    'created'=> $value->created,
                    'created_by'=> $value->created_by,
                    'updated'=> $value->updated,
                    'updated_by'=> $value->updated_by,
                    'archive'=>0
                ];
                // fetch nutritional support in tbl_need_assessment_ns
                if(!empty($value)){
                    DB::table('tbl_need_assessment_ns')
                    ->insert($insData);
                }
                
        }

        $list_attch_rel_for_ns = DB::select("SELECT id FROM `tbl_sales_attachment_relationship` WHERE object_type=5 and LOWER(object_name)='Mealtime'");

        foreach($list_attch_rel_for_ns as $value) {            
            // fetch uuid in member table
            if(!empty($value)){
                DB::table('tbl_sales_attachment_relationship')
                    ->where(['object_type' => 5, 'object_name' => 'Mealtime', 'id' => $value->id])
                    ->update(["object_name" => 'ns_mealtime_files']);
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
