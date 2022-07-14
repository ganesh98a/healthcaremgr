<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOrganisationAdditionalBillingInfoAddCostBook extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_organisation_additional_billing_info', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_organisation_additional_billing_info', 'cost_book_id')) {
                $table->unsignedInteger('cost_book_id')->nullable()->after('cost_code')->comment('tbl_references.id'); 
                $table->foreign('cost_book_id', 'tbl_org_add_bill_info_cb_id_foreign')->references('id')->on('tbl_references');              
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
        Schema::table('tbl_organisation_additional_billing_info', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_organisation_additional_billing_info', 'cost_book_id')) {
                $table->dropForeign('tbl_org_add_bill_info_cb_id_foreign');
                $table->dropColumn('cost_book_id');
            }
        });
    }
}
