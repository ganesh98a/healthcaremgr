<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_shift_location')) {
            Schema::create('tbl_shift_location', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('shiftId')->index();
                $table->string('address',128);
                $table->string('suburb',100)->comment('city');
                $table->unsignedTinyInteger('state');
                $table->string('postal',10);
                $table->string('lat',100);
                $table->string('long',100);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_shift_location');
    }
}
