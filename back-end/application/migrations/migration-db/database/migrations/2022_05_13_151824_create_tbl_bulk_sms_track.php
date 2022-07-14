<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblBulkSmsTrack extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_bulk_sms_track', function (Blueprint $table) {
            $table->bigIncrements('id');            
            $table->text('sms_params')->default(0)->comment('Sending SMS Params to headers'); 
            $table->tinyInteger('status')->default(0)->comment('0-Not sent/1-Inprogress/2-Sent/3-Error');
            $table->tinyInteger('archive')->default(0)->comment('0-Active/1-Inactive');
            $table->text('aws_response')->nullable()->comment('Store response if its failed');          
            $table->unsignedInteger('created_by')->nullable()->comment('The user who initiated the sms or zero if initiated by the system');
            $table->foreign('created_by')->references('id')->on('tbl_member');
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
        Schema::dropIfExists('tbl_bulk_sms_track');
    }
}
