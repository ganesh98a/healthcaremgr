<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftNoTriggerOnShiftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        if (Schema::hasTable('tbl_shift')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_shift_shift_no_after_id`');
            DB::unprepared("CREATE TRIGGER `tbl_shift_shift_no_after_id` BEFORE INSERT ON `tbl_shift` FOR EACH ROW
                IF NEW.shift_no IS NULL or NEW.shift_no=''  THEN 
                SET NEW.shift_no=  (SELECT CONCAT('ST',(select LPAD(d.autoid_data,9,0)  from (select sum(Coalesce((SELECT id FROM tbl_shift ORDER BY id DESC LIMIT 1),0)+ 1) as autoid_data) as d)));
                END IF;");
        }

    }

    public function down(){
        if (Schema::hasTable('tbl_shift')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_shift_shift_no_after_id`');
        }

    }
   
}