<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOpportunityItemsAsUpdatesColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_opportunity_items', function (Blueprint $table) {
            $table->unsignedInteger('line_item_id')->comment('tbl_finance_line_item.id')->after('opportunity_id');
            $table->unsignedInteger('qty')->after('line_item_id');
            $table->double('price', 10, 2)->after('qty');
            $table->double('amount', 10, 2)->comment('qty* price')->after('price');

            if (Schema::hasColumn('tbl_opportunity_items', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('tbl_opportunity_items', 'total')) {
                $table->dropColumn('total');
            }
        });
        Schema::dropIfExists('tbl_opportunity_items_details');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_opportunity_items', function (Blueprint $table) {
            //
        });
    }
}

#$table->dropColumn('created');