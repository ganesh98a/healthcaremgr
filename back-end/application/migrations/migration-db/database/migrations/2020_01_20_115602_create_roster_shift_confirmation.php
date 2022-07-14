<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRosterShiftConfirmation extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_roster_shift_confirmation')) {
            Schema::create('tbl_roster_shift_confirmation', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('roster_shiftId');
                $table->unsignedTinyInteger('confirm_with')->comment('1-Booker, 2-Other, 3-Key Contacts, 4-Billing Contact');
                $table->unsignedTinyInteger('confirm_by')->comment('1- Phone, 2- Email');
                $table->unsignedInteger('confirm_userId')->comment('primary key of booker/key contact and booking contact');
                $table->string('firstname', 32);
                $table->string('lastname', 32);
                $table->string('email', 64);
                $table->string('phone', 20);
                $table->timestamp('created')->default('0000-00-00 00:00:00');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_roster_shift_confirmation');
    }

}
