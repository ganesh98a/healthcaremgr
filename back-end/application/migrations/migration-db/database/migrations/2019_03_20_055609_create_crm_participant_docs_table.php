<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmParticipantDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_participant_docs')) {
            Schema::create('tbl_crm_participant_docs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('crm_participant_id')->index();
                $table->unsignedTinyInteger('type')->comment('1- NDIS, 2- Behavioural 3- Ability 4- Disability 5- other relevant plan 6- service agreement 7- funding consent 8- final service agreement')->index();
                $table->string('title',32);
                $table->string('filename',64);
                $table->timestamp('created')->useCurrent();
                $table->unsignedTinyInteger('archive')->default(0)->comment('0- not archive, 1- archive data(delete)');
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
        Schema::dropIfExists('tbl_crm_participant_docs');
    }
}
