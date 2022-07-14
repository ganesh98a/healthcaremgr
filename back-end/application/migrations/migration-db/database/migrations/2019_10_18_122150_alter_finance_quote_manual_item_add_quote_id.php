<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceQuoteManualItemAddQuoteId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_finance_quote_manual_item', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_quote_manual_item', 'quoteId')) {
                $table->unsignedInteger('quoteId')->comment('primary key tbl_finance_quote')->after('id');
            }
            if (Schema::hasColumn('tbl_finance_quote_manual_item', 'cost')) {
                $table->dropColumn('cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_finance_quote_manual_item', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_quote_manual_item', 'quoteId')) {
                $table->dropColumn('quoteId');
            }

            if (!Schema::hasColumn('tbl_finance_quote_manual_item', 'cost')) {
                $table->double('cost', 14, 2);
            }
        });
    }

}
