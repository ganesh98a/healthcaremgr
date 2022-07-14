<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantPlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_participant_plan')) {
            Schema::create('tbl_participant_plan', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('participantId')->index();
                $table->unsignedTinyInteger('plan_type');
                $table->string('plan_id',20);
                $table->timestamp('start_date')->default('0000-00-00 00:00:00');
                $table->timestamp('end_date')->default('0000-00-00 00:00:00');
                $table->double('total_funding',10,2);
                $table->double('fund_used',10,2);
                $table->double('remaing_fund',10,2);
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
        Schema::dropIfExists('tbl_participant_plan');
    }
}
