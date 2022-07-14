<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimesheetNoTriggerOnTimesheetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        if (Schema::hasTable('tbl_finance_timesheet')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_finance_timesheet_timesheet_no_after_id`');
            DB::unprepared("CREATE TRIGGER `tbl_finance_timesheet_timesheet_no_after_id` BEFORE INSERT ON `tbl_finance_timesheet` FOR EACH ROW
                IF NEW.timesheet_no IS NULL or NEW.timesheet_no=''  THEN 
                SET NEW.timesheet_no=  (SELECT CONCAT('TS',(select LPAD(d.autoid_data,9,0)  from (select sum(Coalesce((SELECT id FROM tbl_finance_timesheet ORDER BY id DESC LIMIT 1),0)+ 1) as autoid_data) as d)));
                END IF;");
        }

    }

    public function down(){
        if (Schema::hasTable('tbl_finance_timesheet')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_finance_timesheet_timesheet_no_after_id`');
        }

    }
   
}