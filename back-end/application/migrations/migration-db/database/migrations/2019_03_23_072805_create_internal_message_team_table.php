<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInternalMessageTeamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_internal_message_team')) {
            Schema::create('tbl_internal_message_team', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('adminId')->index('adminId');
                    $table->string('team_name', 20);
                    $table->string('team_color', 50);
                    $table->string('created', 20);
                    $table->unsignedTinyInteger('archive')->comment('0- not/ 1 - archive');
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
        Schema::dropIfExists('tbl_internal_message_team');
    }
}
