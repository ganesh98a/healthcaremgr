<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceSupportCategoryAsUpdateOtherCategoryInSeeder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_support_category', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_support_category', 'key_name')) {
                $table->string('key_name',150)->after("name");
            }
        });

       Schema::table('tbl_finance_support_category', function (Blueprint $table) {
            $seeder = new FinanceSupportCategory();
            $seeder->run();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_finance_support_category', function (Blueprint $table) {
            //
        });
    }
}
