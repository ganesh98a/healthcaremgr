<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberContactHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_member_contact_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('memberId');
            $table->unsignedSmallInteger('contact_type')->comment('1-Email, 2-Phone, 3-SMS, 4-Chat, 5-Fax');
            $table->mediumText('note',50)->nullable();
            $table->dateTime('time')->default('0000-00-00 00:00:00');
            $table->unsignedSmallInteger('created_by')->comment('login user id');
            $table->dateTime('created');
            $table->timestamp('updated')->useCurrent();
            $table->unsignedTinyInteger('archive')->default(0)->comment('1- archive/delete)'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_member_contact_history');
    }
}
