<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceQuoteChangeColumnTypeOfDate extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_finance_quote', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_quote', 'quote_date')) {
                $table->date('quote_date')->default('0000-00-00')->change();
            }
            if (Schema::hasColumn('tbl_finance_quote', 'valid_until')) {
                $table->date('valid_until')->default('0000-00-00')->change();;
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_finance_quote', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_quote', 'quote_date')) {
                $table->datetime('quote_date')->default('0000-00-00')->change();
            }
            if (Schema::hasColumn('tbl_finance_quote', 'valid_until')) {
                $table->datetime('valid_until')->default('0000-00-00')->change();
            }
        });
    }

}
