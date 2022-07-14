<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRosterNoTriggerOnRosterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        if (Schema::hasTable('tbl_roster')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_roster_roster_no_after_id`');
            DB::unprepared("CREATE TRIGGER `tbl_roster_roster_no_after_id` BEFORE INSERT ON `tbl_roster` FOR EACH ROW
                IF NEW.roster_no IS NULL or NEW.roster_no=''  THEN 
                SET NEW.roster_no=  (SELECT CONCAT('RT',(select LPAD(d.autoid_data,8,0)  from (select sum(Coalesce((SELECT id FROM tbl_roster ORDER BY id DESC LIMIT 1),0)+ 1) as autoid_data) as d)));
                END IF;");
        }

    }

    public function down(){
        if (Schema::hasTable('tbl_roster')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_roster_roster_no_after_id`');
        }

    }
   
}