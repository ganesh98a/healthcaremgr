<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftSiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_shift_site')) {
            Schema::create('tbl_shift_site', function (Blueprint $table) {
                #$table->increments('id');
                $table->unsignedTinyInteger('shiftId');
                $table->unsignedTinyInteger('siteId');
                $table->timestamp('created')->useCurrent();
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
        Schema::dropIfExists('tbl_shift_site');
    }
}
