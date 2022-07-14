<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblCrmRiskAssessmentAddColumnsUpdatedDateUpdatedByIsDelete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_risk_assessment', function(Blueprint $table) {
            if ( ! Schema::hasColumn('tbl_crm_risk_assessment', 'updated_date')) {
                $table->dateTime('updated_date')->nullable();
            }

            if ( ! Schema::hasColumn('tbl_crm_risk_assessment', 'updated_by')) {
                $table->unsignedInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            }

            if ( ! Schema::hasColumn('tbl_crm_risk_assessment', 'is_deleted')) {
                $table->integer('is_deleted')->defult(0)->comment('0 Active/1 Deleted');
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
        Schema::table('tbl_crm_risk_assessment', function(Blueprint $table) {
            if (Schema::hasColumn('tbl_crm_risk_assessment', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }

            if (Schema::hasColumn('tbl_crm_risk_assessment', 'updated_date')) {
                $table->dropColumn('updated_date');
            }

            if (Schema::hasColumn('tbl_crm_risk_assessment', 'is_delete')) {
                $table->dropColumn('is_deleted');
            }

        });
    }
}
