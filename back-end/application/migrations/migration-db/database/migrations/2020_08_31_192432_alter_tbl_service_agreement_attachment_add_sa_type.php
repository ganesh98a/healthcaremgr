<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblServiceAgreementAttachmentAddSaType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_service_agreement_attachment', function (Blueprint $table) {
            $table->unsignedInteger('service_agreement_type')->nullable()->comment('1 - NDIS / 2 - Support coordination')->after('type');
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
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'service_agreement_type')) {
                $table->dropColumn('service_agreement_type');
            }
        });
    }
}
