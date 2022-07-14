<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCommentsColumnTblCrmParticipantDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('tbl_crm_participant_docs')) {
        Schema::table('tbl_crm_participant_docs', function (Blueprint $table) {
              $table->unsignedInteger('type')->comment('1- NDIS, 2- Behavioural 3- Ability 4- Disability 5- other relevant plan 6- service agreement 7- funding consent 8- final service agreement')->change();
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
      if(Schema::hasTable('tbl_crm_participant_docs') && Schema::hasColumn('tbl_crm_participant_docs', 'type') ) {
        Schema::table('tbl_crm_participant_docs', function (Blueprint $table) {
            $table->unsignedInteger('type')->comment('1- NDIS, 2- Behavioural 3- Ability 4- Disability 5- other relevant plan 6- service agreement 7- funding consent 8- final service agreement')->change();
        });
      }
    }
}
