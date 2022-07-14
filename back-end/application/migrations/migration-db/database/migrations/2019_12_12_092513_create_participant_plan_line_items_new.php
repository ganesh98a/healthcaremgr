<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantPlanLineItemsNew extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_participant_plan_line_items')) {
            Schema::create('tbl_participant_plan_line_items', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('participantId')->comment('primary key tbl_participant');
                $table->unsignedInteger('participant_planId')->comment('primary key tbl_participant_plan');
                $table->unsignedInteger('line_itemId')->comment('primary key tbl_finance_line_item');
                $table->double('total_funding', 10, 2);
                $table->double('fund_used', 10, 2);
                $table->double('fund_remaining', 10, 2);
                $table->dateTime('created')->comment('0- Not/ 1 - Yes');
                $table->dateTime('updated')->comment('0- Not/ 1 - Yes');
                $table->unsignedTinyInteger('archive')->comment('0- Not/ 1 - Yes');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_participant_plan_line_items');
    }

}
