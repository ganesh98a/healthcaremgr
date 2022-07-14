<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantStageNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_participant_stage_notes')) {
            Schema::create('tbl_crm_participant_stage_notes', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('crm_participant_id')->nullable();
                $table->unsignedInteger('stage_id')->nullable();
                $table->string('notes',64)->nullable();
                $table->unsignedTinyInteger('status')->nullable()->comment('1- Active, 0- Inactive');
                $table->timestamp('created_at1')->useCurrent();
               $table->timestamp('updated_at1')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('tbl_crm_participant_stage_notes');
    }
}
