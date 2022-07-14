<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantPlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_participant_plan')) {
            Schema::create('tbl_crm_participant_plan', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('crm_participant_id')->index();
                $table->unsignedTinyInteger('plan_type');
                $table->string('plan_id',20);
                $table->string('start_date',20);
                $table->string('end_date',20);
                $table->double('total_funding',10,2)->default('0.00');
                $table->double('fund_used',10,2)->default('0.00');
                $table->double('remaing_fund',10,2)->default('0.00');
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
        Schema::dropIfExists('tbl_crm_participant_plan');
    }
}
