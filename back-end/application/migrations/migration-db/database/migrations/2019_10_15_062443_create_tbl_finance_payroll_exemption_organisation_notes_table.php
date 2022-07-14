<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblFinancePayrollExemptionOrganisationNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_finance_payroll_exemption_organisation_notes')) {
            Schema::create('tbl_finance_payroll_exemption_organisation_notes', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('fpeo_id')->comment('auto increment id of tbl_finance_payroll_exemption_organisation table.');
                $table->text('notes')->nullable();
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
                $table->unsignedTinyInteger('archive')->default('0');
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
        Schema::dropIfExists('tbl_finance_payroll_exemption_organisation_notes');
    }
}
