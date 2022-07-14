<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblFinanceLineItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_finance_line_item', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('funding_type')->comment('priamry key tbl_finance_funding_type');
            $table->unsignedInteger('support_registration_group')->comment('priamry key tbl_finance_support_registration_group')->nullable();
            $table->unsignedInteger('support_category')->comment('priamry key tbl_finance_support_category');
            $table->unsignedInteger('support_purpose')->nullable()->comment('primay key of tbl_finance_support_purpose');
            $table->unsignedInteger('support_type')->nullable()->comment('primay key of tbl_finance_support_type');
            $table->unsignedInteger('support_outcome_domain')->nullable()->comment('priamry key tbl_finance_support_outcome_domain');
            $table->string('category_ref', 100)->nullable();
            $table->string('line_item_number', 100)->comment('Should be unique');
            $table->string('line_item_name', 255);
            $table->mediumText('description')->nullable();
            $table->mediumText('needs')->nullable();
            $table->unsignedInteger('quote_required')->comment('0 -Not/1- Yes');
            $table->unsignedInteger('price_control')->comment('0 -Not/1- Yes');
            $table->unsignedInteger('travel_required')->comment('0 -Not/1- Yes');
            $table->unsignedInteger('cancellation_fees')->comment('0 -Not/1- Yes');
            $table->unsignedInteger('ndis_reporting')->comment('0 -Not/1- Yes');
            $table->unsignedInteger('non_f2f')->comment('0 -Not/1- Yes');
            $table->unsignedInteger('schedule_constraint')->comment('0 -Not/1- Yes');
            $table->unsignedInteger('member_ratio')->nullable();
            $table->unsignedInteger('participant_ratio')->nullable();
            $table->unsignedInteger('levelId')->default(0)->comment('tbl_classification_level auto increment id');
            $table->unsignedInteger('pay_pointId')->default(0)->comment('tbl_classification_point auto increment id');
            $table->unsignedInteger('measure_by')->nullable()->comment('priamry key tbl_finance_measure');
            $table->unsignedSmallInteger('units')->nullable();
            $table->unsignedInteger('oncall_provided')->comment('0 -Not/1- Yes');
            $table->unsignedSmallInteger('weekday')->default(0)->comment('0 -Not/1- Yes');
            $table->unsignedSmallInteger('saturday')->default(0)->comment('0 -Not/1- Yes');
            $table->unsignedSmallInteger('sunday')->default(0)->comment('0 -Not/1- Yes');
            $table->unsignedSmallInteger('public_holiday')->comment('0 -Not/1- Yes');
            $table->unsignedSmallInteger('daytime')->default(0)->comment('DayTime 0- Not/1- Yes');
            $table->unsignedSmallInteger('evening')->default(0)->comment('Evening 0- Not/1- Yes');
            $table->unsignedSmallInteger('overnight')->default(0)->comment('Active Overnight - 0- Not/1- Yes');
            $table->unsignedSmallInteger('sleepover')->default(0)->comment('Sleepover - 0-Not/1- Yes');
            $table->unsignedSmallInteger('archive')->default(0)->comment('0 -Not/1- Yes');
            $table->unsignedInteger('created_by')->nullable()->comment('priamry key tbl_users');
            $table->foreign('created_by')->references('id')->on('tbl_users')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('updated_by')->nullable()->comment('priamry key tbl_users');
            $table->foreign('updated_by')->references('id')->on('tbl_users')->onUpdate('cascade')->onDelete('cascade');
            $table->datetime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->datetime('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_finance_line_item', function (Blueprint $table) {
            Schema::dropIfExists('tbl_finance_line_item');
        });
    }
}
