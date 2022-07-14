<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblNotificationAddEntityTypeAndId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_notification', function (Blueprint $table) {
            $table->unsignedInteger('entity_type')->nullable()->comment('1-opportunity/2-lead/3- service agreement/4-needs assessment/5-Risk assessment/6-ServiceAgreement Contract');
            $table->unsignedInteger('entity_id')->nullable()->comment('primary key of as per relation_type (tbl_opportunity|tbl_leads|tbl_service_agreement|tbl_need_assessment|tbl_crm_risk_assessment|tbl_service_agreement_attachment)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_notification', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_notification', 'entity_type')) {
                $table->dropColumn('entity_type');
            }
            if (Schema::hasColumn('tbl_notification', 'entity_id')) {
                $table->dropColumn('entity_id');
            }
        });
    }
}
