<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOpportunityItemsLineItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_opportunity_items', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_opportunity_items','line_item_id')) {
                $table->renameColumn('line_item_id', 'line_item_price_id');
            }            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_opportunity_items', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_opportunity_items','line_item_price_id')) {
                $table->renameColumn('line_item_price_id', 'line_item_id');
            }
        });
    }
}
