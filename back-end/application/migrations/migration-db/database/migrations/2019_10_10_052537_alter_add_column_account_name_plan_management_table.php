<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnAccountNamePlanManagementTable extends Migration
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
              $table->string('account_name',50);
              $table->unsignedInteger('payment_method')->default('1')->comment('1-bpay, 2-aus post, 3-bank');
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
      if (Schema::hasTable('tbl_plan_management') && Schema::hasColumn('tbl_plan_management','account_name') && Schema::hasColumn('tbl_plan_management','payment_method')) {
        Schema::table('tbl_plan_management', function (Blueprint $table) {
            $table->dropColumn('account_name');
            $table->dropColumn('payment_method');
        });
      }
    }
}
