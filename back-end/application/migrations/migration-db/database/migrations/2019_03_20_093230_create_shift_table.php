<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_shift')) {
            Schema::create('tbl_shift', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedTinyInteger('booked_by')->comment('1= Site / Home, 2= Participant, 3 =Location');
                $table->date('shift_date')->nullable();
                $table->dateTime('start_time')->default('0000-00-00 00:00:00');
                $table->dateTime('end_time')->default('0000-00-00 00:00:00');
                $table->string('expenses',100);
                $table->unsignedTinyInteger('so')->comment('1- Yes, 0- No');
                $table->unsignedTinyInteger('ao')->comment('1- Yes, 0- No');
                $table->unsignedTinyInteger('eco')->comment('1- Yes, 0- No');
                $table->double('price',10,2)->default('0.00');
                $table->unsignedTinyInteger('allocate_pre_member')->comment('1- Yes, 0- No');
                $table->unsignedTinyInteger('autofill_shift')->comment('1- Yes, 0- No');
                $table->unsignedTinyInteger('push_to_app')->comment('1- Yes, 0- No, 2 Return from App');
                $table->unsignedInteger('status')->comment('1-Unfilled/ 2- Unconfirmed / 3- Quote / 4 -Rejected / 5 -Cancelled / 6 - Completed / 7 - Confirmed / 8 - Archive');
                $table->unsignedTinyInteger('shift_amendment')->comment('1- shift has amendment / 2- no amendment');
                $table->timestamp('created')->useCurrent();
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('tbl_shift');
    }
}
