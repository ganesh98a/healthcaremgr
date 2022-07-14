<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblPlanMgmtToAddColumnsImageUrl extends Migration
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
                if(!Schema::hasColumn('tbl_plan_management','image_url')){
                    $table->longText('image_url')->nullable()->comment('follow up notes');
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
                if(Schema::hasColumn('tbl_plan_management','image_url')){
                    $table->dropColumn('image_url');
                } 
            });

        }
    }
}
