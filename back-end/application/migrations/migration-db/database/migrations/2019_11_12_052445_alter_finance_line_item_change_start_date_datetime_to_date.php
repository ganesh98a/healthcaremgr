<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceLineItemChangeStartDateDatetimeToDate extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_finance_line_item', function (Blueprint $table) {
            $table->date('start_date')->comment('If start date && end date within current date = Active/If start date is in future = Inactive/If end date is in Past = Archived')->change();
            $table->date('end_date')->comment('')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_finance_line_item', function (Blueprint $table) {
            $table->datetime('start_date')->change();
            $table->datetime('end_date')->change();
        });
    }

}
