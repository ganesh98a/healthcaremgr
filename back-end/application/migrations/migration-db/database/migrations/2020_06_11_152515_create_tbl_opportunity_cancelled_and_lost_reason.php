<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblOpportunityCancelledAndLostReason extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_opportunity_cancelled_and_lost_reason', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('opportunity_id')->comment('tbl_opportunity.id');
            $table->unsignedInteger('reason')->comment('tbl_references.id and with type tbl_reference_data_type.key_name = "cancel_and_lost_reason_opp"');
            $table->text('reason_note');
            $table->dateTime('created');
            $table->unsignedSmallInteger('archive')->comment("0-No/1-Yes");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_opportunity_cancelled_and_lost_reason');
    }

}
