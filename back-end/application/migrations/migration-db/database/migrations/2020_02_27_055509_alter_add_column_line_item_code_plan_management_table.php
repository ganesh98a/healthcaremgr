<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnLineItemCodePlanManagementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_plan_management', function (Blueprint $table) {
          if (!Schema::hasColumn('tbl_plan_management','line_item_code') ) {
            $table->unsignedInteger('line_item_code')->nullable()->default(0);
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
          if (Schema::hasColumn('tbl_plan_management','line_item_code')) {
            $table->dropColumn('line_item_code');
          }
        });
    }
}
