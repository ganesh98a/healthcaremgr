<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantPlanBreakdown extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_participant_plan_breakdown', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('participant_id');
            $table->string('support_item_number',50);
            $table->string('support_item_name',200);
            $table->string('amount',100);
            $table->datetime('created')->default('0000-00-00 00:00:00');
            $table->timestamp('updated')->useCurrent();
            $table->integer('plan_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_participant_plan_breakdown');
    }
}
