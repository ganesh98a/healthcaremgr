<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceAgreementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_service_agreement', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('opportunity_id')->comment("tbl_opportunity.id");
            $table->unsignedInteger('status')->comment("0- Draft, 1- Awaiting Approval, 2- Approved, 3- Accepted, 4- Declined, 5- Active");
            $table->unsignedInteger('owner')->comment("tbl_member.id, only admin");
            $table->unsignedInteger('account')->comment("tbl_person.id / tbl_organisaition.id");
            $table->unsignedInteger('account_type')->comment("1-Person/2-organisation");
            $table->float('grand_total',10,2);
            $table->float('sub_total',10,2);
            $table->float('tax',10,2);
            $table->dateTime('customer_signed_date');
            $table->unsignedInteger('signed_by');
            $table->dateTime('contract_start_date');
            $table->dateTime('contract_end_date');
            $table->dateTime('plan_start_date');
            $table->dateTime('plan_end_date');
            $table->unsignedSmallInteger('archive')->comment("0-No/1-Yes");
            $table->timestamp('created')->useCurrent();
            $table->unsignedInteger('created_by');
            $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->unsignedInteger('updated_by');
        });
    }  

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_service_agreement');
    }
}
