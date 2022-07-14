<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ArchivePostponedOptionInUnsuccessfulReason extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {            
        $items = DB::select("SELECT * FROM `tbl_references` where code='unsuccessful_group_booking_reason' and display_name = 'Postponed'");
        
        foreach($items as $value) {
            if(!empty($value) && $value->display_name == 'Postponed'){
            DB::table('tbl_references')
            ->where('id',$value->id)
            ->update(["archive" => 1]);
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
