<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableShiftUsers extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_shift_users')) {
            Schema::create('tbl_shift_users', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('shiftId')->comment('tbl_shift auto increment id');
                $table->unsignedSmallInteger('user_type')->comment('1 - reserve/2 - reserve/3 - reserve/4- org/5 - sub-org/6 - reserve in quote/7- house');
                $table->unsignedInteger('user_for')->comment('tbl_house/tbl_organisation auto increment id');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
                $table->unsignedSmallInteger('archive')->default('0')->comment('1- Yes, 0- No');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_shift_users');
    }

}
