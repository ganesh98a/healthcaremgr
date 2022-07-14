<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_shift_member')) {
            Schema::create('tbl_shift_member', function (Blueprint $table) {
                $table->increments('shiftId')->index();
                $table->unsignedInteger('memberId')->index();
                $table->unsignedInteger('status')->comment('1-Pending/ 2 - Rejected / 3 - Accepted / 4 - Cancelled');
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
        Schema::dropIfExists('tbl_shift_member');
    }
}
