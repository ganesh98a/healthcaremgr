<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableOrganisationAddBillingInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_organisation', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_organisation', 'communication_mode')) {
                $table->smallInteger('communication_mode')->nullable()->default(1)->comment('1 => Email, 2 => One Email per Invoice, Post');
            }
            if (!Schema::hasColumn('tbl_organisation', 'site_discount')) {
                $table->smallInteger('site_discount')->comment('1 => Applicable, 0 => Not Applicable');
            }
            if (!Schema::hasColumn('tbl_organisation', 'billing_info_same_as_parent')) {
                $table->smallInteger('billing_info_same_as_parent')->comment('1 => yes, 0 => No');
            }
            if (Schema::hasColumn('tbl_organisation', 'payroll_tax')) {
                $table->smallInteger('payroll_tax')->default(1)->change();
            }
            if (Schema::hasColumn('tbl_organisation', 'gst')) {
                $table->smallInteger('gst')->default(1)->change();
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
            if (Schema::hasColumn('tbl_organisation', 'communication_mode')) {
                $table->dropColumn('communication_mode');
            }
            if (Schema::hasColumn('tbl_organisation', 'site_discount')) {
                $table->dropColumn('site_discount');
            }
            if (Schema::hasColumn('tbl_organisation', 'billing_info_same_as_parent')) {
                $table->dropColumn('billing_info_same_as_parent');
            }
        });
    }
}
