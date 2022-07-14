<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumunXeroContactIdPlanManagementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_plan_management', function (Blueprint $table) {
          if (!Schema::hasColumn('tbl_plan_management','xero_contact_id') ) {
            $table->string('xero_contact_id',250)->nullable();
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
          if (Schema::hasColumn('tbl_plan_management','xero_contact_id')) {
            $table->dropColumn('xero_contact_id');
          }
        });
    }
}
