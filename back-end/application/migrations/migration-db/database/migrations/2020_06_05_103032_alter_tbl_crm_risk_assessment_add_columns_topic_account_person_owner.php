<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblCrmRiskAssessmentAddColumnsTopicAccountPersonOwner extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_risk_assessment', function (Blueprint $table) {
            if ( ! Schema::hasColumn('tbl_crm_risk_assessment', 'topic')) {
                $table->string('topic', 255)->after("reference_id");
            }

            if ( ! Schema::hasColumn('tbl_crm_risk_assessment', 'account_type')) {
                $table->unsignedInteger('account_type')->nullable()->comment("1 - person / 2 - Organization")->after("topic");
            }

            if ( ! Schema::hasColumn('tbl_crm_risk_assessment', 'account_id')) {
                $table->bigInteger('account_id')->nullable()->unsigned()->comment("tbl_person.id / tbl_organization.id")->after("account_type");
            }

            if ( ! Schema::hasColumn('tbl_crm_risk_assessment', 'owner_id')) {
                $table->unsignedInteger('owner_id')->nullable()->comment('tbl_member.id admin id')->after("account_id");
                $table->foreign('owner_id')->references('id')->on('tbl_member')->onUpdate('CASCADE')->onDelete('CASCADE');
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
        Schema::table('tbl_crm_risk_assessment', function (Blueprint $table) {

            if (Schema::hasColumn('tbl_crm_risk_assessment', 'topic')) {
                $table->dropColumn('topic');
            }

            if (Schema::hasColumn('tbl_crm_risk_assessment', 'account_type')) {
                $table->dropColumn('account_type');
            }

            if (Schema::hasColumn('tbl_crm_risk_assessment', 'account_id')) {
                $table->dropColumn('account_id');
            }

            if (Schema::hasColumn('tbl_crm_risk_assessment', 'owner_id')) {
                $table->dropForeign(['owner_id']);
                $table->dropColumn('owner_id');
            }
        });
    }
}
