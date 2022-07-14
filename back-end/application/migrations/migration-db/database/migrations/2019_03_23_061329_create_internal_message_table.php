<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInternalMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_internal_message')) {
            Schema::create('tbl_internal_message', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedSmallInteger('companyId')->index('companyId');
                    $table->string('title', 200)->index('title');
                    $table->unsignedInteger('is_block')->comment('0- not / 1 - yes');
                    $table->dateTime('created');
                    $table->dateTime('updated');
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
        Schema::dropIfExists('tbl_internal_message');
    }
}
