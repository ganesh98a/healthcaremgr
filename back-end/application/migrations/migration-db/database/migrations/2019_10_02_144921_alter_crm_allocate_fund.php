<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmAllocateFund extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_crm_allocate_fund')) {
            Schema::rename('tbl_crm_allocate_fund', 'tbl_crm_participant_plan_breakdown');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_crm_participant_plan_breakdown')) {
            Schema::rename('tbl_crm_participant_plan_breakdown', 'tbl_crm_allocate_fund');
        }
      
    }
}
