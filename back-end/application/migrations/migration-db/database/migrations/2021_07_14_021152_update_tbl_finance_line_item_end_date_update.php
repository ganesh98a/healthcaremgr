<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblFinanceLineItemEndDateUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_line_item', function (Blueprint $table) {
            DB::table('tbl_finance_line_item')
                ->where(['end_date'=> '2021-07-14'])
                ->update(["end_date" => '2021-06-30']);
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
            DB::table('tbl_finance_line_item')
                ->where(['end_date'=> '2021-06-30'])
                ->update(["end_date" => '2021-07-14']);
        });
    }
}
