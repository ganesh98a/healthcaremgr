<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceMeasureTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {


        if (!Schema::hasTable('tbl_finance_measure')) {
            Schema::create('tbl_finance_measure', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 150);
                $table->string('kay_name', 150);
                $table->smallInteger('archive')->comment('0 -Not/1 - Archive');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_finance_measure');
    }

}
