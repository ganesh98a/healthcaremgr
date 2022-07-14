<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmRelationsParticipantTableInitialRunSeeder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('tbl_crm_relations_participant', function (Blueprint $table) {
            $seeder = new CrmRelationsParticipant();
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
        Schema::table('tbl_crm_relations_participant', function (Blueprint $table) {
            //
        });
    }
}
