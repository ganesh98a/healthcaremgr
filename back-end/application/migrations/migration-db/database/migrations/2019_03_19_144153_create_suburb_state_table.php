<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuburbStateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_suburb_state')) {
            Schema::create('tbl_suburb_state', function (Blueprint $table) {
                $table->increments('id');
                $table->string('suburb',100);
                $table->string('state',50);
                $table->unsignedInteger('stateId');
                $table->unsignedInteger('postcode');
                $table->string('latitude',200);
                $table->string('longitude',200);
            });
            DB::statement('ALTER TABLE `tbl_suburb_state` CHANGE `postcode` `postcode` int(4) unsigned zerofill NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_suburb_state');
    }
}
