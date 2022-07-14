<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterParticipantCareNotTobookAddType extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::dropIfExists('tbl_participant_care_not_tobook');

        if (!Schema::hasTable('tbl_participant_care_not_tobook')) {
            Schema::create('tbl_participant_care_not_tobook', function (Blueprint $table) {
                $table->unsignedInteger('participantId')->comment('primary key of table "tbl_participant"');
                $table->unsignedInteger('gender')->comment('1 - male/2 - female');
                $table->unsignedInteger('requirementId')->comment('primary key of table "tbl_ethnicity","tbl_religious_beliefs"');
                $table->unsignedSmallInteger('type')->comment('1 - ethnicity/2 - religious');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_participant_care_not_tobook', function (Blueprint $table) {
            //
        });
    }

}
