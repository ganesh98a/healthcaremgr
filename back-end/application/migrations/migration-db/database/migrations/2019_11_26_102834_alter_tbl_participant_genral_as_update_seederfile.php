<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblParticipantGenralAsUpdateSeederfile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_participant_genral', function (Blueprint $table) {
          $seeder = new ParticipantGenralSeeder();
          $seeder->run();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_participant_genral', function (Blueprint $table) {
            //
        });
    }
}
