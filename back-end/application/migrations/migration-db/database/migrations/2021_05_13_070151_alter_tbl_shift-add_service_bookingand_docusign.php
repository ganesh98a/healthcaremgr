<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftAddServiceBookingandDocusign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift', 'service_booking_id')) {
                $table->unsignedInteger('service_booking_id')->nullable()->after("service_agreement_id")->comment('tbl_service_booking.id');
                // $table->foreign('service_booking_id')->references('id')->on('tbl_service_booking')->onUpdate('cascade')->onDelete('cascade');
            }
            if (!Schema::hasColumn('tbl_shift', 'docusign_id')) {
                $table->unsignedInteger('docusign_id')->nullable()->after("service_agreement_id")->comment('tbl_service_agreement_attachment.id');
                // $table->foreign('docusign_id')->references('id')->on('tbl_service_agreement_attachment')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'service_booking_id')) {
                // $table->dropForeign(['service_booking_id']);
                $table->dropColumn('service_booking_id');
            }
            if (Schema::hasColumn('tbl_shift', 'docusign_id')) {
                // $table->dropForeign(['docusign_id']);
                $table->dropColumn('docusign_id');
            }
        });
    }
}
