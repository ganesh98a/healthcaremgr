<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRenamePlanManagementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('plan_management_vendor')) {
          Schema::rename('plan_management_vendor', 'tbl_plan_management_vendor');
      }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      if (Schema::hasTable('tbl_plan_management_vendor')) {
          Schema::rename('tbl_plan_management_vendor', 'plan_management_vendor');
      }
    }
}
