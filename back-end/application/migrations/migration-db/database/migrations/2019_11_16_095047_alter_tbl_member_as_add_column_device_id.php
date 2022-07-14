<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberAsAddColumnDeviceId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member', function (Blueprint $table) {
             $table->unsignedInteger('device_id')->default(0)->comment('unique login device id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_member')) {
            Schema::table('tbl_member', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_member','device_id')){
                    $table->dropColumn('device_id');
                } 
            });

        }
    }
}
