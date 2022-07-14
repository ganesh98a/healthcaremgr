<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnSetDefaultvalueCreatedCrmParticipantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_participant', function (Blueprint $table) {
              if (Schema::hasColumn('tbl_crm_participant','created')) {
                DB::unprepared("ALTER TABLE `tbl_crm_participant`
                    CHANGE `created` `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ");
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
        Schema::table('tbl_crm_participant', function (Blueprint $table) {

        });
    }
}
