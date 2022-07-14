<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceQuoteItemRemoveChargeByAndAddPriceType extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_finance_quote_item', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_quote_item', 'price_type')) {
                $table->string('price_type', 200)->comment('(only for information purpose)price type upper_price_limit, national_price_limit and national_very_price_limit')->after('qty');
            }

            if (Schema::hasColumn('tbl_finance_quote_item', 'charge_by')) {
                $table->dropColumn('charge_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_finance_quote_item', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_quote_item', 'price_type')) {
                $table->dropColumn('price_type');
            }
        });
    }

}
