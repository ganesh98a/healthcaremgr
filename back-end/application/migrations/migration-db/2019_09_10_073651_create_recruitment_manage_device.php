<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentManageDevice extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_recruitment_manage_device', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('deviceId')->comment('auto increment id of tbl_recruitment_device table.');
            $table->unsignedInteger('taskId')->comment('auto increment id of tbl_recruitment_task table.');
            $table->unsignedInteger('applicant_id')->comment('auto increment id of tbl_recruitment_applicant table.');
            $table->unsignedInteger('allocate_at')->comment('device when allocate');
            $table->unsignedTinyInteger('archive')->default('0')->comment('1- Yes, 0- Not');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_recruitment_manage_device');
    }

}
