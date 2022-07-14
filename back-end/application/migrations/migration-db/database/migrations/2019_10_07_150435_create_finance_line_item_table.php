<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceLineItemTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_finance_line_item', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('funding_type')->comment('priamry key tbl_finance_funding_type');
            $table->unsignedInteger('support_registration_group')->comment('priamry key tbl_finance_support_registration_group');
            $table->unsignedInteger('support_category')->comment('priamry key tbl_finance_support_category');
            $table->unsignedInteger('support_outcome_domain')->comment('priamry key tbl_finance_support_outcome_domain');
            $table->string('line_item_number', 100)->comment('Should be unique');
            $table->string('line_item_name', 255);

            $table->datetime('start_date');
            $table->datetime('end_date');

            $table->text('description')->comment('0 -Not/1- Yes');

            $table->unsignedInteger('quote_required')->comment('0 -Not/1- Yes');
            $table->unsignedInteger('price_control')->comment('0 -Not/1- Yes');
            $table->unsignedInteger('travel_required')->comment('0 -Not/1- Yes');
            $table->unsignedInteger('cancellation_fees')->comment('0 -Not/1- Yes');
            $table->unsignedInteger('ndis_reporting')->comment('0 -Not/1- Yes');
            $table->unsignedInteger('non_f2f')->comment('0 -Not/1- Yes');

            $table->double('upper_price_limit', 14, 2);
            $table->double('national_price_limit', 14, 2);
            $table->double('national_very_price_limit', 14, 2);

            $table->unsignedInteger('schedule_constraint')->comment('0 -Not/1- Yes');
            $table->unsignedInteger('public_holiday')->comment('0 -Not/1- Yes');
            
            $table->unsignedInteger('member_ratio');
            $table->unsignedInteger('participant_ratio');

            $table->datetime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->datetime('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_finance_line_item');
    }

}
