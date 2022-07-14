<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanManagementVendorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan_management_vendor', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('biller_code');
            $table->unsignedInteger('billpay_code');
            $table->unsignedInteger('bsb_number');
            $table->unsignedInteger('bank_account_no');
            $table->datetime('date_added')->useCurrent();
            $table->string('company_name',50);
            $table->string('bank_account_name',50);
        });
    }
  

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan_management_vendor');
    }
}
