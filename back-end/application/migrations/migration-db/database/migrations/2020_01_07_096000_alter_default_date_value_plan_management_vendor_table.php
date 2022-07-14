<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDefaultDateValuePlanManagementVendorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_plan_management_vendor')) {
        Schema::table('tbl_plan_management_vendor', function (Blueprint $table) {
          DB::unprepared("ALTER TABLE `tbl_plan_management_vendor`
              CHANGE `date_added` `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP  ");

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
        Schema::table('tbl_plan_management_vendor', function (Blueprint $table) {
            
        });

    }
}
