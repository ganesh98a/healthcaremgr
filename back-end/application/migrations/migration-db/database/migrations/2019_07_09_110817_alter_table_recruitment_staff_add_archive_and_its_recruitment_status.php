<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitmentStaffAddArchiveAndItsRecruitmentStatus extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_staff', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_staff')) {
                $table->unsignedInteger('its_recruitment_admin')->comment('1 - Yes/ 0 - Not');
                $table->unsignedInteger('archive')->comment('1 - Yes/ 0 - Not');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_staff', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_task')) {
                $table->dropColumn('its_recruitment_admin');
                $table->dropColumn('archive');
            }
        });
    }

}
