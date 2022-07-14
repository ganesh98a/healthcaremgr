<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinancePayrollExemptionOrganisationForCreatedByCoulmnuAddedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_finance_payroll_exemption_organisation')) {
            Schema::table('tbl_finance_payroll_exemption_organisation', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_finance_payroll_exemption_organisation','created_by')){
                    $table->unsignedInteger('created_by')->nullable()->default(0)->comment('tbl_member auto increment column id');
                }
                if(Schema::hasColumn('tbl_finance_payroll_exemption_organisation','status')){
                    $table->unsignedSmallInteger('status')->default(1)->comment('1-active/expired/expired soon/or future payroll exemption, 2- inactive')->change();
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
        if (Schema::hasTable('tbl_finance_payroll_exemption_organisation')) {
            Schema::table('tbl_finance_payroll_exemption_organisation', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_finance_payroll_exemption_organisation','created_by')){
                    $table->dropColumn('created_by');
                } 
            });
        }
    }
}
