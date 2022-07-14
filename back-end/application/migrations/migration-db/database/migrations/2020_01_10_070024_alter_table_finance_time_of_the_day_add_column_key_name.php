<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableFinanceTimeOfTheDayAddColumnKeyName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_finance_time_of_the_day')) {
            Schema::table('tbl_finance_time_of_the_day', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_finance_time_of_the_day', 'key_name')) {
                    $table->string("key_name")->nullable()->after('short_name')->comment('unique name used for get id or filter in code');
                }
            });
            $seeder = new FinanceTimeOfTheDay();
            $seeder->run();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_finance_time_of_the_day')) {
            Schema::table('tbl_finance_time_of_the_day', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_finance_time_of_the_day', 'key_name')) {
                    $table->dropColumn("key_name");
                }
            });
        }
    }
}
