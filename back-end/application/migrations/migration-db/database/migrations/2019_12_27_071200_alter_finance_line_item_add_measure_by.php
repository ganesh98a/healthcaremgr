<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceLineItemAddMeasureBy extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_finance_line_item', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_line_item', 'measure_by')) {
                $table->unsignedInteger('measure_by')->comment('priamry key tbl_finance_measure')->after('participant_ratio');
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
            if (Schema::hasColumn('tbl_finance_line_item', 'measure_by')) {
                $table->dropColumn('measure_by');
            }
        });
    }

}
