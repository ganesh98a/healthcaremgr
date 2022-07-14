<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceLineItemAddCategoryRef extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_line_item', function (Blueprint $table) {
            if ( ! Schema::hasColumn('tbl_finance_line_item', 'category_ref')) {
                $table->string('category_ref', 100)->after('line_item_name');
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
            //
        });
    }
}
