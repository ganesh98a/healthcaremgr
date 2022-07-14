<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTypeColumnCommentCrmParticipantDocsTable extends Migration
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
            $table->unsignedInteger('type')->comment('1- NDIS Plan, 2- Behavioral Support Plan, 3- Ability, 4- Disability, 5- Other Relevant Plan, 6- Service Agreement Document, 7- Funding Consent, 8- Final Service Agreement Document, 9-Other Attachments')->change();
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
      Schema::table('tbl_crm_participant_docs', function (Blueprint $table) {
            $table->unsignedInteger('type')->comment('1- NDIS, 2- Behavioural 3- Ability 4- Disability 5- other relevant plan 6- service agreement 7- funding consent 8- final service agreement')->change();
      });
    }
}
