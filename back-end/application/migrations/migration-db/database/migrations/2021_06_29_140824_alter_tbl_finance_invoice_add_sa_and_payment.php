<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinanceInvoiceAddSaAndPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_finance_invoice', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_finance_invoice', 'sa_id')) {
                $table->unsignedInteger('sa_id')->nullable()->after("status")->comment('reference of tbl_service_agreement.id');
                $table->foreign('sa_id')->references('id')->on('tbl_service_agreement')->onDelete('cascade');
            }
            if (!Schema::hasColumn('tbl_finance_invoice', 'sa_managed_type')) {
                $table->unsignedTinyInteger('sa_managed_type')->nullable()->after("sa_id")->comment('service agreement managed type 1 - Portal / 2 - Plan / 3 - Self');
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
        Schema::table('tbl_finance_invoice', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'sa_id')) {
                $table->dropForeign(['sa_id']);
                $table->dropColumn('sa_id');
            }
            if (Schema::hasColumn('tbl_shift', 'sa_managed_type')) {
                $table->dropColumn('sa_managed_type');
            }
        });
    }
}
