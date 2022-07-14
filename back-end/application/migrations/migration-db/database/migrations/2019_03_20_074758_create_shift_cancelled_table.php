<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftCancelledTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_shift_cancelled')) {
            Schema::create('tbl_shift_cancelled', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('shiftId');
                $table->string('cancel_type',100)->comment('member / participant / kin / org / site / booker');
                $table->unsignedInteger('cancel_by')->comment('MemberId / ParticipantId');
                $table->unsignedInteger('cancel_method')->comment('1 - Email / 2 - Call / 3 -SMS');
                $table->string('person_name',100);
                $table->text('reason');
                $table->timestamp('created')->useCurrent();
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
        Schema::dropIfExists('tbl_shift_cancelled');
    }
}
