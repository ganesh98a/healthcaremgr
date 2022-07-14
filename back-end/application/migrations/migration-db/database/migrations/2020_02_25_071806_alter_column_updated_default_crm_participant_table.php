<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnUpdatedDefaultCrmParticipantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_particiant', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_crm_particiant','updated')) {
              DB::unprepared("ALTER TABLE `tbl_crm_participant` CHANGE `updated` `updated` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;");
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_crm_particiant', function (Blueprint $table) {
            //
        });
    }
}
