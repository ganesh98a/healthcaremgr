<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOrganisationAsAddColumnBillPayBy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_organisation', function (Blueprint $table) {
            $table->unsignedSmallInteger('bill_pay_by')->unsigned()->comment('1- Parent Org,2- Sub-org,3-Self')->after('is_first_time_login');
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
            if (Schema::hasColumn('tbl_organisation','bill_pay_by')) {
               $table->dropColumn('bill_pay_by');
           }
       });
    }
}
