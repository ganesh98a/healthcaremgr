<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddIndexTblShiftNdisLineItemChangeLineItemId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift_ndis_line_item', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('tbl_shift_ndis_line_item');

            if(array_key_exists("line_item_id", $indexesFound))
                $table->dropIndex('line_item_id');

            if(array_key_exists("line_item_price_id", $indexesFound))
                $table->dropIndex('line_item_price_id');

            $table->unsignedInteger('line_item_id')->index('line_item_id')->comment('tbl_finance_line_item.id')->change();
            $table->unsignedInteger('line_item_price_id')->index('line_item_price_id')->nullable()->comment('tbl_finance_line_item_price.id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_shift_ndis_line_item', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('tbl_shift_ndis_line_item');

            if(array_key_exists("line_item_id", $indexesFound))
                $table->dropIndex('line_item_id');

            if(array_key_exists("line_item_price_id", $indexesFound))
                $table->dropIndex('line_item_price_id');          
        });
    }
}
