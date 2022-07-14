<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedBackIdTriggerOnFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        if (Schema::hasTable('tbl_fms_feedback')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_fms_feedback_feedback_id_after_id`');
            DB::unprepared("CREATE TRIGGER `tbl_fms_feedback_feedback_id_after_id` BEFORE INSERT ON `tbl_fms_feedback` FOR EACH ROW
                IF NEW.feedback_id IS NULL or NEW.feedback_id=''  THEN 
                SET NEW.feedback_id=  (SELECT CONCAT('FK',(select LPAD(d.autoid_data,9,0)  from (select sum(Coalesce((SELECT id FROM tbl_fms_feedback ORDER BY id DESC LIMIT 1),0)+ 1) as autoid_data) as d)));
                END IF;");
        }

    }

    public function down(){
        if (Schema::hasTable('tbl_fms_feedback')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_fms_feedback_feedback_id_after_id`');
        }

    }
   
}