<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblCrmRiskAssessmentChangeStatusFinalToActive extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_crm_risk_assessment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_crm_risk_assessment', 'status')) {
                $table->unsignedSmallInteger('status')->comment('1 - Draft, 2- Active(Final), 3 - Inactive')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_crm_risk_assessment', function (Blueprint $table) {
            
        });
    }

}
