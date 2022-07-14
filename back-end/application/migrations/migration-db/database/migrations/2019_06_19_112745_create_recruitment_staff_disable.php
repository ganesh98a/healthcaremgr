<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentStaffDisable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_recruitment_staff_disable_history')) {
            Schema::create('tbl_recruitment_staff_disable', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('recruitment_staff_id');
                $table->string('disable_account', 30);
                $table->string('account_allocated_type', 30);
                $table->text('relevant_note');
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
        Schema::dropIfExists('tbl_recruitment_staff_disable');
    }

}
