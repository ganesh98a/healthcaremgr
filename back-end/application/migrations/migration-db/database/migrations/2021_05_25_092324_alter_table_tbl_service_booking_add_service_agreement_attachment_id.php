<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTblServiceBookingAddServiceAgreementAttachmentId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() 
    {
        Schema::table('tbl_service_booking', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_service_booking', 'service_agreement_attachment_id')) {
                $table->unsignedInteger('service_agreement_attachment_id')->nullable()->after("service_booking_number")->comments('tbl_service_agreement_attachment.id');
                $table->foreign('service_agreement_attachment_id')->references('id')->on('tbl_service_agreement_attachment')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('tbl_service_booking', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_service_booking', 'service_agreement_attachment_id')) {
                $table->dropForeign(['service_agreement_attachment_id']);
                $table->dropColumn('service_agreement_attachment_id');
            }
        });
    }
}
