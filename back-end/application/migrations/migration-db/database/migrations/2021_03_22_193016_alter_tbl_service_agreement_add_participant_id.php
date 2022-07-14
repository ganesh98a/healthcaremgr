<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblServiceAgreementAddParticipantId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_service_agreement', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_service_agreement', 'transfer_aides_description')) {
                $table->unsignedInteger('participant_id')->nullable()->comment('reference id of tbl_participants_master.id')->after('archive');
                $table->foreign('participant_id')->references('id')->on('tbl_participants_master')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('tbl_service_agreement', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_service_agreement', 'participant_id')) {
                $table->dropColumn('participant_id');
            }
        });
    }
}
