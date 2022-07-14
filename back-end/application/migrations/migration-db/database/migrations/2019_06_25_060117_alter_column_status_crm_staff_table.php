<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnStatusCrmStaffTable extends Migration
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
            $table->unsignedTinyInteger('status')->comment('0 - Inactive, 1 - Active');
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
      if (Schema::hasTable('tbl_crm_staff') && Schema::hasColumn('tbl_crm_staff', 'status')) {
        Schema::table('tbl_crm_staff', function (Blueprint $table) {
            $table->dropColumn('status');
        });
      }
    }
}
