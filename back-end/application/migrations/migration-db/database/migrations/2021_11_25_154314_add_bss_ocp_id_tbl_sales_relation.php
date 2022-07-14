<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBssOcpIdTblSalesRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('tbl_sales_relation', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_sales_relation', 'bss_ocp_id')) {
                $table->unsignedInteger('bss_ocp_id')->comment('ocp_id_for_migration')->after('id');
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
        Schema::table('tbl_sales_relation', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_sales_relation', 'bss_ocp_id')) {
                $table->dropColumn('bss_ocp_id');
            }
        });
    }
}
