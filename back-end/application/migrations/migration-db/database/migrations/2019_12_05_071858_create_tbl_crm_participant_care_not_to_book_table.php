<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblCrmParticipantCareNotToBookTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_crm_participant_care_not_to_book')) {
            Schema::create('tbl_crm_participant_care_not_to_book', function (Blueprint $table) {
                $table->unsignedInteger('crm_participant_id')->comment('primary key of table "tbl_crm_participant"');
                $table->unsignedInteger('carer_type')->comment('primary key of table "tbl_ethnicity","tbl_religious_beliefs"');
                $table->unsignedSmallInteger('type')->comment('1 for ethnicity/2 for religious');
                $table->unsignedSmallInteger('archive')->comment('0 for No/1 for Yes');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_crm_participant_care_not_to_book');
    }

}
