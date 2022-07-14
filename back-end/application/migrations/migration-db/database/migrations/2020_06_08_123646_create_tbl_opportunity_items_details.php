<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblOpportunityItemsDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_opportunity_items_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_id')->comment("tbl_opportunity_items.id");
            $table->unsignedInteger('line_item_id')->comment('tbl_finance_line_item.id');
            $table->unsignedInteger('qty');
            $table->double('amount', 10, 2);
            $table->unsignedSmallInteger('archive')->comment("0-No/1-Yes");
            $table->timestamp('created')->useCurrent();
            $table->unsignedInteger('created_by');
            $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->unsignedInteger('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_opportunity_items_details');
    }
}
