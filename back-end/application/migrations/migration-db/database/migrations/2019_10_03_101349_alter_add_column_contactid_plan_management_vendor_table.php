<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnContactidPlanManagementVendorTable extends Migration
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
            $table->string('contact_id',50);
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
      if (Schema::hasTable('tbl_plan_management_vendor') && Schema::hasColumn('tbl_plan_management_vendor','contact_id')) {
        Schema::table('tbl_plan_management_vendor', function (Blueprint $table) {
            $table->dropColumn('contact_id');
        });
      }
    }
}
