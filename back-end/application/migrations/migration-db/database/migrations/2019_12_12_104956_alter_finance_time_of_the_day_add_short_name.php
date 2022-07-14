<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFinanceTimeOfTheDayAddShortName extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_finance_time_of_the_day', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_time_of_the_day', 'short_name')) {
                $table->string('short_name', 150)->after('name');
                $table->string("key_name")->nullable()->comment('unique name used for get id or filter in code');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_finance_time_of_the_day', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_time_of_the_day', 'short_name')) {
                $table->dropColumn('short_name');
                $table->dropColumn('key_name');
            }
        });
    }

}
