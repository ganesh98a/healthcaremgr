<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceQuoteManualItemRemoveTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_finance_quote_manual_item', function (Blueprint $table) {
            Schema::dropIfExists('tbl_finance_quote_manual_item');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_finance_quote_manual_item', function (Blueprint $table) {
            //
        });
    }

}
