<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberAvailabilityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_member_availability')) {
            Schema::create('tbl_member_availability', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('memberId')->index('memberId');
                    $table->string('title', 50);
                    $table->unsignedTinyInteger('is_default')->comment('1- Yes, 2- No');
                    $table->unsignedTinyInteger('status');
                    $table->dateTime('start_date')->nullable();
                    $table->dateTime('end_date')->nullable();
                    $table->text('first_week');
                    $table->text('second_week');
                    $table->unsignedTinyInteger('flexible_availability')->comment('1- Yes, 0- No');
                    $table->unsignedSmallInteger('flexible_km');
                    $table->unsignedSmallInteger('travel_km');
                    $table->dateTime('updated')->default('0000-00-00 00:00:00');
                    $table->unsignedTinyInteger('archive')->comment('1- Delete');
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
        Schema::dropIfExists('tbl_member_availability');
    }
}
