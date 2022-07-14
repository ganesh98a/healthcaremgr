<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceLineItemAddPayPointAndLavel extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_finance_line_item', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_line_item', 'levelId')) {
                $table->unsignedInteger('levelId')->default(0)->comment('tbl_classification_level auto increment id')->after('national_very_price_limit');
            }

            if (!Schema::hasColumn('tbl_finance_line_item', 'pay_pointId')) {
                $table->unsignedInteger('pay_pointId')->default(0)->comment('tbl_classification_point auto increment id')->after('levelId');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_finance_line_item', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_line_item', 'levelId')) {
                $table->dropColumn('levelId');
            }

            if (Schema::hasColumn('tbl_finance_line_item', 'pay_pointId')) {
                $table->dropColumn('pay_pointId');
            }
        });
    }

}
