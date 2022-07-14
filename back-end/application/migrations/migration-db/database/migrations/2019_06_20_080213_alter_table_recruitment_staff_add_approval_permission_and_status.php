<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitmentStaffAddApprovalPermissionAndStatus extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('tbl_recruitment_staff')) {
            Schema::table('tbl_recruitment_staff', function (Blueprint $table) {
                $table->unsignedTinyInteger('approval_permission')->comment('0 - No, 1 - Yes');
                $table->unsignedTinyInteger('status')->comment('0 - No, 1 - Yes');
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
        if (Schema::hasTable('tbl_recruitment_staff')) {
            Schema::table('tbl_recruitment_staff', function (Blueprint $table) {
                $table->dropColumn('approval_permission');
                $table->dropColumn('status');
                $table->dropColumn('created');
            });
        }
    }

}
