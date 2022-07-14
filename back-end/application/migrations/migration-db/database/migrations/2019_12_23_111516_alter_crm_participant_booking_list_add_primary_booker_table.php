<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmParticipantBookingListAddPrimaryBookerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_participant_booking_list', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_crm_participant_booking_list', 'primary_booker')) {
                $table->unsignedSmallInteger('primary_booker')->comment('1- Primary/2-Secondary')->after('email');
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
        Schema::table('tbl_crm_participant_booking_list', function (Blueprint $table) {
             if (Schema::hasColumn('tbl_crm_participant_booking_list', 'PrimaryBooker')) {
                $table->dropColumn('primary_booker');
            }
        });
    }
}
