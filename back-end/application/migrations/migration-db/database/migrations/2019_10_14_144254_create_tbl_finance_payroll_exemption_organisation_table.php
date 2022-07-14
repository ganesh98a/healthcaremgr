<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblFinancePayrollExemptionOrganisationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_finance_payroll_exemption_organisation')) {
            Schema::create('tbl_finance_payroll_exemption_organisation', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('organisation_id')->comment('auto increment id of tbl_organisation table.');
                $table->string('file_title',100)->nullable();
                $table->string('file_path',255)->nullable();
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->date('valid_from')->default('0000-00-00');
                $table->date('valid_to')->default('0000-00-00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
                $table->unsignedSmallInteger('status')->default('1')->comment('1-active/expired/expired soon, 2- inactive');
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
        Schema::dropIfExists('tbl_finance_payroll_exemption_organisation');
    }
}
