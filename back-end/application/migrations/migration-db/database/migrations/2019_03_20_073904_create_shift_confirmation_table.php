<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftConfirmationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_shift_confirmation')) {
            Schema::create('tbl_shift_confirmation', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('shiftId');
                $table->unsignedTinyInteger('confirm_with')->comment('1-Booker, 2-Other, 3-Key Contacts, 4-Billing Contact');
                $table->unsignedTinyInteger('confirm_by')->comment('1- Phone, 2- Email');
                $table->string('firstname',32);
                $table->string('lastname',32);
                $table->string('email',64);
                $table->string('phone',20);
                $table->timestamp('confirmed_with_allocated')->default('0000-00-00 00:00:00');
                $table->timestamp('confirmed_on')->default('0000-00-00 00:00:00');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_shift_confirmation');
    }
}
