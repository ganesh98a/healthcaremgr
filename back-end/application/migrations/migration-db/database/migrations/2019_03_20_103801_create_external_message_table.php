<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExternalMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_external_message')) {
            Schema::create('tbl_external_message', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('companyId');
                $table->string('title',228)->index();
                $table->unsignedTinyInteger('is_block')->comment('0- not / 1 - yes');
                $table->timestamp('created')->default('0000-00-00 00:00:00');
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
        Schema::dropIfExists('tbl_external_message');
    }
}
