<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFilenameColumnsSizeCrmParticipantDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_participant_docs', function (Blueprint $table) {
            Schema::table('tbl_crm_participant_docs', function (Blueprint $table) {
                DB::unprepared("ALTER TABLE `tbl_crm_participant_docs`
              MODIFY  `filename` VARCHAR(255) ");
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_crm_participant_docs', function (Blueprint $table) {
            //
        });
    }
}
