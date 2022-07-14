<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnServiceareaCrmStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_crm_staff')) {
        Schema::table('tbl_crm_staff', function (Blueprint $table) {
            $table->string('service_area')->default('NDIS');
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
      if (Schema::hasTable('tbl_crm_staff')) {
        Schema::table('tbl_crm_staff', function (Blueprint $table) {
            $table->dropColumn('service_area');
        });
      }
    }
}
