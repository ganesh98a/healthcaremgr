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
        Schema::create('tbl_shift_sms_log', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('shift_id')->nullable()->comment('1 - tbl_shift.id');
            $table->foreign('shift_id')->references('id')->on('tbl_shift');
            $table->unsignedInteger('sender_id')->nullable()->comment('the user who initiated the sms. reference of tbl_member');
            $table->foreign('sender_id')->references('id')->on('tbl_member');
            $table->unsignedInteger('recipient_id')->nullable()->comment('the user received the sms.reference of tbl_shift_member');
            $table->foreign('recipient_id')->references('id')->on('tbl_shift_member');
            $table->mediumText('content')->nullable();
            $table->mediumText('response')->nullable()->comment('response');
            $table->unsignedInteger('created_by')->nullable()->comment('the user who initiated the sms or zero if initiated by the system');
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
        Schema::dropIfExists('tbl_shift_sms_log');
    }
}
