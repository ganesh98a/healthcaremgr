<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_shift_feedback')) {
            Schema::create('tbl_shift_feedback', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('shift_id');
                $table->unsignedInteger('member_id');
                $table->unsignedTinyInteger('incident_type');
                $table->text('what_happen');
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
        Schema::dropIfExists('tbl_shift_feedback');
    }
}
