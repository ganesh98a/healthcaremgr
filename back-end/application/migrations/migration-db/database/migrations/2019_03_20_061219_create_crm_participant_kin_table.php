<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantKinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_participant_kin')) {
            Schema::create('tbl_crm_participant_kin', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('crm_participant_id')->index();
                $table->string('firstname',32);
                $table->string('lastname',32);
                $table->string('relation',24);
                $table->string('phone',20);
                $table->string('email',64);
                $table->unsignedTinyInteger('primary_kin')->comment('1- Primary, 2- Secondary');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('tbl_crm_participant_kin');
    }
}
