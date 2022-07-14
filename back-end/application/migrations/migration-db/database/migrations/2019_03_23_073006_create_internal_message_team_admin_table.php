<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInternalMessageTeamAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_internal_message_team_admin')) {
            Schema::create('tbl_internal_message_team_admin', function(Blueprint $table)
                {
                    $table->unsignedInteger('teamId')->index('teamId');
                    $table->unsignedInteger('adminId')->index('adminId');
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
        Schema::dropIfExists('tbl_internal_message_team_admin');
    }
}
