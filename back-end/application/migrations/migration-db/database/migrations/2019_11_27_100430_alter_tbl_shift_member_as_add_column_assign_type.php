<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftMemberAsAddColumnAssignType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift_member', function (Blueprint $table) {
            $table->unsignedTinyInteger("assign_type")->after('status')->comment('1- Manually, 0-Auto');
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_shift_member', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_member', 'assign_type')) {
                $table->dropColumn('assign_type');
            } 
        });
    }
}
