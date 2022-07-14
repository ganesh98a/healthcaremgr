<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblAssessmentAssistanceMealtime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $list_of_assistance = DB::select("SELECT * FROM `tbl_assessment_assistance` where LOWER(title)='Mealtime'");
        
        foreach($list_of_assistance as $value) { 
            // fetch nutritional_support in tbl_assessment_assistance
            if(!empty($value)){
                DB::table('tbl_assessment_assistance')
                ->where(["id"=>$value->id, "archive"=>0])
                ->update(["title" => "Nutritional Support", "key_name"=>"nutritional_support"]);
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
