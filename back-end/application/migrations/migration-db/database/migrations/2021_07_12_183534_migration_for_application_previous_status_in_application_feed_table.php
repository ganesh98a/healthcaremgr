<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrationForApplicationPreviousStatusInApplicationFeedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $list_of_application = DB::select("SELECT id FROM `tbl_recruitment_applicant_applied_application` 
        where application_process_status =8 and archive=0 ORDER BY `id`  DESC");
        
            foreach($list_of_application as $value) { 
                $application_field_history = DB::select("SELECT * FROM `tbl_application_field_history` where field='status' and application_id=".$value->id." ORDER BY `tbl_application_field_history`.`id` DESC limit 1");
                
                // fetch prev application status in tbl_recruitment_applicant_applied_application
                if(!empty($application_field_history) && !empty($application_field_history[0]->prev_val)){
                    DB::table('tbl_recruitment_applicant_applied_application')
                    ->where("id",$value->id)
                    ->update(["prev_application_process_status" => $application_field_history[0]->prev_val]);
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
