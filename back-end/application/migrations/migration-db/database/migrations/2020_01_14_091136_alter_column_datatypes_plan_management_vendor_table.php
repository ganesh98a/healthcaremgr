<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnDatatypesPlanManagementVendorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('tbl_plan_management_vendor', function (Blueprint $table) {
          DB::unprepared("ALTER TABLE `tbl_plan_management_vendor`
              CHANGE `bsb_number` `bsb_number` VARCHAR(20) NOT NULL  AFTER `billpay_code`,
              CHANGE `bank_account_no` `bank_account_no`  VARCHAR(20)  NOT NULL AFTER `bsb_number`,
              CHANGE `company_name` `company_name`  VARCHAR(100)  NOT NULL AFTER `date_added`,
              CHANGE `bank_account_name` `bank_account_name`  VARCHAR(100)  NOT NULL AFTER `company_name`");

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_plan_management_vendor', function (Blueprint $table) {

        });
    }
}
