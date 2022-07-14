<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddOldColumnCrmParticipantDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_participant_docs', function (Blueprint $table) {
            if (
                !Schema::hasColumn('tbl_participant_docs', 'is_old_doc')
            ) {
                $table->unsignedInteger('is_old_doc')->default(0)->comment('0-New Document, 1-Old Document');
            }
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
            if (Schema::hasColumn('tbl_participant_docs', 'is_old_doc')) {
                $table->dropColumn('is_old_doc');
            }
        });
    }
}
