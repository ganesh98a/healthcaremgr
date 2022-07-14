<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCostBookKeyNameTblReference extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_references', function (Blueprint $table) {
            DB::table('tbl_references')
            ->where(['display_name'=> 'NDIS Level 1'])
            ->update(["key_name" => 'ndis_level_1']);

            DB::table('tbl_references')
            ->where(['display_name'=> 'NDIS Level 2'])
            ->update(["key_name" => 'ndis_level_2']);

            DB::table('tbl_references')
            ->where(['display_name'=> 'NDIS Karista'])
            ->update(["key_name" => 'ndis_karista']);

            DB::table('tbl_references')
            ->where(['display_name'=> 'Home Care'])
            ->update(["key_name" => 'home_care']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_references', function (Blueprint $table) {
            //
        });
    }
}
