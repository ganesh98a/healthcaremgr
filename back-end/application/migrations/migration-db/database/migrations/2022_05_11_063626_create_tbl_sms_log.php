<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblSmsLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_sms_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedSmallInteger('message_sent_type')->default(0)->comment('1-Single,2-Bulk');
            $table->unsignedSmallInteger('applicant_id')->nullable();
            $table->unsignedSmallInteger('application_id')->nullable();
            $table->unsignedSmallInteger('entity_type')->default(0)->comment('1-Job application, 2-Activity block, 3-Group booking, 4-OA');            
            $table->unsignedBigInteger('phone_number')->nullable()->comment('Recipient Phone number');
            $table->text('status_desc')->nullable()->comment('This column helps to track the current progress for with custom message');
            $table->text('message')->nullable()->comment('Message text');
            $table->text('aws_response')->default(0)->comment('Store response if its failed');          
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
        Schema::dropIfExists('tbl_sms_logs');
    }
}
