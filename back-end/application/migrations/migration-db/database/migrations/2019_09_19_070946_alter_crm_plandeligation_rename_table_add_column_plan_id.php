<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmPlandeligationRenameTableAddColumnPlanId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        if (Schema::hasTable('tbl_crm_plandeligation')) {
            Schema::rename('tbl_crm_plandeligation', 'tbl_crm_allocate_fund');           
        }
        if (Schema::hasTable('tbl_crm_allocate_fund')) {
            Schema::table('tbl_crm_allocate_fund', function (Blueprint $table) {          
                $table->integer('plan_id');
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
        if (Schema::hasTable('tbl_crm_plandeligation')) {
            Schema::rename('tbl_crm_allocate_fund', 'tbl_crm_plandeligation');
        }
        if (Schema::hasTable('tbl_crm_allocate_fund')) {
            Schema::table('tbl_crm_allocate_fund', function (Blueprint $table) {          
                $table->dropColumn('plan_id');
            });
        }
    }
}
