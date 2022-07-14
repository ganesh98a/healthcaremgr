<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTblServiceAgreementAttachmentDocType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_service_agreement_attachment', function (Blueprint $table) {
            # update tbl_finance_time_of_the_day for document type
            DB::statement("UPDATE `tbl_service_agreement_attachment` SET `document_type` = service_agreement_type + 1 WHERE 1 ");
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
            DB::statement("UPDATE `tbl_service_agreement_attachment` SET `document_type` = document_type - 1 WHERE 1 ");
        });
    }
}
