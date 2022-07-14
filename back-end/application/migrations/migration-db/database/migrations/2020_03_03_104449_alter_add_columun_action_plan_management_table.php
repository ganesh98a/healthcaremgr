<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumunActionPlanManagementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_plan_management', function (Blueprint $table) {
          if (!Schema::hasColumn('tbl_plan_management','xero_action') ) {
            $table->unsignedTinyInteger('xero_action')->nullable()->comment('1 - NDIS Paid, 2 - NDIS Rejected');
          }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_plan_management', function (Blueprint $table) {
          if (Schema::hasColumn('tbl_plan_management','xero_action')) {
            $table->dropColumn('xero_action');
          }
        });
    }
}
