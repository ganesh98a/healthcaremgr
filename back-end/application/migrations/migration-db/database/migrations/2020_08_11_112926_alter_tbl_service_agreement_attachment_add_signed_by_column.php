<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblServiceAgreementAttachmentAddSignedByColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_service_agreement_attachment', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_service_agreement_attachment', 'signed_by')) {
                $table->unsignedSmallInteger('signed_by')->nullable()->comment("")->after('to');
                // $table->foreign('signed_by')->references('id')->on('tbl_person')->onDelete('cascade');
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
        Schema::table('tbl_service_agreement_attachment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'signed_by')) {
                if (Schema::hasColumn('tbl_service_agreement_attachment', 'signed_by')) {
                    // Drop foreign key
                    // $table->dropForeign(['signed_by']);
                }
                $table->dropColumn('signed_by');
            }
        });
    }
}
