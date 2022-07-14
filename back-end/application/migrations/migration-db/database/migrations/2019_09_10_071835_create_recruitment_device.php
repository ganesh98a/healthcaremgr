<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentDevice extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_recruitment_device', function (Blueprint $table) {
            $table->increments('id');
            $table->string('device_name', 200);
            $table->string('device_number', 200);
            $table->unsignedInteger('device_location')->comment('auto increment id of tbl_recruitment_location table.');
            $table->unsignedInteger('is_offline')->comment('1 - offline / 0 - avaiable || assigned');
            $table->dateTime('created')->default('0000-00-00 00:00:00');
            $table->timestamp('updated')->useCurrent();
            $table->unsignedTinyInteger('archive')->default('0')->comment('1- Yes, 0- Not');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_recruitment_device');
    }

}
