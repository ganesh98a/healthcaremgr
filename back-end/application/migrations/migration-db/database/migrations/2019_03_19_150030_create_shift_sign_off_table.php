<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftSignOffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_shift_sign_off')) {
            Schema::create('tbl_shift_sign_off', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('shiftId');
                $table->string('member_signature',200);
                $table->string('approval_signature',200);
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
        Schema::dropIfExists('tbl_shift_sign_off');
    }
}
