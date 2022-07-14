<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateLineItemSupportTypeAsNull extends Migration
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
            ->where(['support_type'=> 0])
            ->update(["support_type" => NULL]);
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
