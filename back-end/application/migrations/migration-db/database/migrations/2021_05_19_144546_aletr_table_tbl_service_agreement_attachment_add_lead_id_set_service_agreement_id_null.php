<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AletrTableTblServiceAgreementAttachmentAddLeadIdSetServiceAgreementIdNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //account ype null
        Schema::table('tbl_service_agreement_attachment', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_service_agreement_attachment', 'lead_id')) {
                $table->unsignedBigInteger('lead_id')->after('account_type')->nullable()->default(null)->comment('tbl_leads.id');
                $table->foreign('lead_id')->references('id')->on('tbl_leads')->onUpdate("set null")->onDelete("set null");
            }
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'account_id')) {
                $table->unsignedInteger('account_id')->nullable()->change();
            }
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'account_type')) {
                $table->unsignedInteger('account_type')->nullable()->change();
            }
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'service_agreement_id')) {
                $table->unsignedInteger('service_agreement_id')->nullable()->change();
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
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'lead_id')) {
                $table->dropForeign(['lead_id']);
                $table->dropColumn('lead_id');
            }
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'account_id')) {
                $table->unsignedInteger('account_id')->nullable(false)->change();
            }
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'account_type')) {
                $table->unsignedInteger('account_type')->nullable(false)->change();
            }
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'service_agreement_id')) {
                $table->unsignedInteger('service_agreement_id')->nullable(false)->change();
            }
        });
    }
}
