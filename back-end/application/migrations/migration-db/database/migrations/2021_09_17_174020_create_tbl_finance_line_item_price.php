<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblFinanceLineItemPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_finance_line_item_price', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('line_item_id')->comment('priamry key tbl_finance_line_item');
            $table->foreign('line_item_id')->references('id')->on('tbl_finance_line_item')->onUpdate('cascade')->onDelete('cascade');
            $table->date('start_date')->nullable()->comment('If start date && end date within current date = Active/If start date is in future = Inactive/If end date is in Past = Archived');
            $table->date('end_date')->nullable()->comment('');
            $table->double('upper_price_limit', 14, 2);
            $table->unsignedSmallInteger('archive')->default(0)->comment('0 -Not/1- Yes');
            $table->unsignedInteger('created_by')->nullable()->comment('priamry key tbl_users');
            $table->foreign('created_by')->references('id')->on('tbl_users')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('updated_by')->nullable()->comment('priamry key tbl_users');
            $table->foreign('updated_by')->references('id')->on('tbl_users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_finance_line_item_price');
    }
}
