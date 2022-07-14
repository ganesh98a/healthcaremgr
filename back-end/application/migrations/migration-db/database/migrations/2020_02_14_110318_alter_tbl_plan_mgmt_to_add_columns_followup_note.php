<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblPlanMgmtToAddColumnsFollowupNote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        	if (Schema::hasTable('tbl_plan_management')) {
            Schema::table('tbl_plan_management', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_plan_management','followup_note')){
                    $table->string('followup_note', 250);
                }
            });

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_plan_management')) {
            Schema::table('tbl_plan_management', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_plan_management','followup_note')){
                    $table->dropColumn('followup_note');
                } 
            });
        }
    }
}
