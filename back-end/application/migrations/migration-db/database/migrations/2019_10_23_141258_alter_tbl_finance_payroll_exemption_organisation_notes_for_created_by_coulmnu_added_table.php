<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinancePayrollExemptionOrganisationNotesForCreatedByCoulmnuAddedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_finance_payroll_exemption_organisation_notes')) {
            Schema::table('tbl_finance_payroll_exemption_organisation_notes', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_finance_payroll_exemption_organisation_notes','created_by')){
                    $table->unsignedInteger('created_by')->nullable()->default(0)->comment('tbl_member auto increment column id');
                }
            });

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_finance_payroll_exemption_organisation_notes')) {
            Schema::table('tbl_finance_payroll_exemption_organisation_notes', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_finance_payroll_exemption_organisation_notes','created_by')){
                    $table->dropColumn('created_by');
                } 
            });
        }
    }
}
