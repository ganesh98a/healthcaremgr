<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeekDayTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_week_day', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('short_name', 50);
            $table->unsignedInteger('js_formate_number');
            $table->datetime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_week_day');
    }

}
