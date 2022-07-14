<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRunSeederFinanceTimeOfTheDayForAddShort extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_finance_time_of_the_day', function (Blueprint $table) {
            $seeder = new FinanceTimeOfTheDay();
            $seeder->run();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_finance_time_of_the_day', function (Blueprint $table) {
            //
        });
    }

}
