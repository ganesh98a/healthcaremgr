<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberAvailabilityListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_member_availability_list')) {
            Schema::create('tbl_member_availability_list', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('member_availability_id');
                    $table->unsignedInteger('memberId')->index('memberId');
                    $table->unsignedTinyInteger('is_default')->comment('1- Yes, 2- No');
                    $table->unsignedTinyInteger('status')->comment('is_open or filled');
                    $table->string('availability_type');
                    $table->date('availability_date')->nullable();
                    $table->string('availability_week', 100)->nullable();
                    $table->time('availability_start_time');
                    $table->unsignedTinyInteger('flexible_availability')->comment('1- Yes, 0- No');
                    $table->unsignedSmallInteger('flexible_km');
                    $table->unsignedSmallInteger('travel_km');
                    $table->dateTime('created');
                    $table->dateTime('updated')->default('0000-00-00 00:00:00');
                    $table->unsignedTinyInteger('archive')->comment('1- Delete');
                    $table->unsignedTinyInteger('run_mode')->comment('1- Cron, 0- OCS');
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
        Schema::dropIfExists('tbl_member_availability_list');
    }
}
