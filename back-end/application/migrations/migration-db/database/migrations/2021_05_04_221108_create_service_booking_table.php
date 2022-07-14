<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceBookingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_service_booking', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('service_booking_number')->comment('Manual Entry');
            $table->integer('service_booking_creator')->comment('1=Participant/Agent 2=ONCALL');
            $table->integer('funding')->comment('Manual Entry');
            $table->text('status')->comment('inactive,active,proposed');
            $table->text('date_submitted')->comment('date_submitted manual entry');
            $table->text('service_agreement_type')->comment('tbl reference data type Service Agreement Type');
            $table->integer('related_service_agreement_id')->comment('tbl_service_agreement');
            $table->boolean('is_received_signed_service_booking');
            $table->boolean('archive')->default(0)->comment('Soft delete Not=0, Yes=1');
            $table->unsignedBigInteger('created_by')->nullable()->comment('tbl_members.id');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('tbl_members.id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_service_booking');
    }
}
