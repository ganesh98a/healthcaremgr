<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertShiftBreakTypeAddIntrept extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $object = array("type" => "25", "key_name" => "interrupted_sleepover", "display_name" => "Interrupted Sleepover","start_date" => "", "created" => "2021-08-09 10:11:12", "archive" => 0);
        DB::table('tbl_references')->updateOrInsert(['type' => $object['type'],'display_name' => $object['display_name']], $object);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('tbl_references')->where("key_name", "interrupted_sleepover")->update(["archive" => 1]);
    }
}
