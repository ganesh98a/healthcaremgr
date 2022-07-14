<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBssOcpIdScOcpIdTblSalesActivity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_sales_activity', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_sales_activity', 'bss_ocp_id')) {
                $table->unsignedInteger('bss_ocp_id')->comment('bss_ocp_id_for_migration')->after('id');
            }
            if (!Schema::hasColumn('tbl_sales_activity', 'sc_ocp_id')) {
                $table->unsignedInteger('sc_ocp_id')->comment('sc_ocp_id_for_migration')->after('id');
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
        Schema::table('tbl_sales_activity', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_sales_activity', 'bss_ocp_id')) {
                 $table->dropColumn('bss_ocp_id');
            }
            if (Schema::hasColumn('tbl_sales_activity', 'sc_ocp_id')) {
                $table->dropColumn('sc_ocp_id');
           }
        });
    }
}
