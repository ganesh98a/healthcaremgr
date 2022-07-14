<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvoiceToColumnTblOrganisation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_organisation', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_organisation', 'invoice_to')) {
                $table->string('invoice_to',128)->comment('billing account name')->after('name');
            }
            if (!Schema::hasColumn('tbl_organisation', 'billing_same_as_parent')) {
                $table->boolean('billing_same_as_parent')->comment('is billing information taken from parent')->default(0);
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
        Schema::table('tbl_organisation', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_organisation', 'invoice_to')) {
                 $table->dropColumn('invoice_to');
            }
            if (Schema::hasColumn('tbl_organisation', 'billing_same_as_parent')) {
                $table->dropColumn('billing_same_as_parent');
           }
        });
    }
}
