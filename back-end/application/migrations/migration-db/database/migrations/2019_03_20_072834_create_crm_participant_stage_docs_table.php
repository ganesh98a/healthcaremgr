<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantStageDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_participant_stage_docs')) {
            Schema::create('tbl_crm_participant_stage_docs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('crm_participant_id')->nullable();
                $table->unsignedInteger('stage_id');
                $table->string('title',100);
                $table->string('file_path',64);
                $table->unsignedTinyInteger('archive')->nullable()->comment('0- not archive, 1- archive data(delete)');
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
        Schema::dropIfExists('tbl_crm_participant_stage_docs');
    }
}
