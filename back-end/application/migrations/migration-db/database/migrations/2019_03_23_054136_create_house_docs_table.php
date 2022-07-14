<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHouseDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_house_docs')) {
            Schema::create('tbl_house_docs', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('houseId')->index('houseId');
                    $table->string('filename', 64);
                    $table->string('title', 64);
                    $table->date('expiry');
                    $table->string('created', 20);
                    $table->unsignedTinyInteger('archive')->default(0)->comment('1- Yes, 0- No');
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
        Schema::dropIfExists('tbl_house_docs');
    }
}
