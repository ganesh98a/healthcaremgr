<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLineItemUnits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_line_item', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_line_item', 'units')) {
                $table->unsignedSmallInteger('units')->after('pay_pointId');
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
        Schema::table('tbl_finance_line_item', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_line_item', 'units')) {
                $table->dropColumn('units');
            }           
        });
    }
}
