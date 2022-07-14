<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberPositionAwardAsAddNewColumnWorkArea extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member_position_award', function (Blueprint $table) {
             $table->unsignedInteger('work_area')->after('memberId');
            
             $table->renameColumn('position', 'pay_point');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_member_position_award', function (Blueprint $table) {
            if(Schema::hasColumn('tbl_member_position_award','work_area')){
                    $table->dropColumn('work_area');
            } 
            $table->renameColumn('pay_point', 'position');
        });
    }
}
