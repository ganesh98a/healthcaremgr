<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceQuoteItemAddQuoteId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_finance_quote_item', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_quote_item', 'quoteId')) {
                $table->unsignedInteger('quoteId')->comment('primary key tbl_finance_quote')->after('id');
            }
            if (!Schema::hasColumn('tbl_finance_quote_item', 'qty')) {
                $table->unsignedInteger('qty')->after('itemId');
            }
            if (!Schema::hasColumn('tbl_finance_quote_item', 'cost')) {
                $table->double('cost', 14, 2)->after('qty');
            }
            if (!Schema::hasColumn('tbl_finance_quote_item', 'item_name')) {
                $table->string('item_name', 255)->after('itemId');
            }
            if (!Schema::hasColumn('tbl_finance_quote_item', 'description')) {
                $table->text('description')->after('item_name');
            }
            if (!Schema::hasColumn('tbl_finance_quote_item', 'charge_by')) {
                $table->unsignedInteger('charge_by')->after('description');
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
            if (Schema::hasColumn('tbl_finance_quote_item', 'quoteId')) {
                $table->dropColumn('quoteId');
            }
            if (Schema::hasColumn('tbl_finance_quote_item', 'qty')) {
                $table->dropColumn('qty');
            }
            if (Schema::hasColumn('tbl_finance_quote_item', 'cost')) {
                $table->dropColumn('cost');
            }

            if (Schema::hasColumn('tbl_finance_quote_item', 'item_name')) {
                $table->dropColumn('item_name');
            }
            if (Schema::hasColumn('tbl_finance_quote_item', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('tbl_finance_quote_item', 'charge_by')) {
                $table->dropColumn('charge_by');
            }
        });
    }

}
