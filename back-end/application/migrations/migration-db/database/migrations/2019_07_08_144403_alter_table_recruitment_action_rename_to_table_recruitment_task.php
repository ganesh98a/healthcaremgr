<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitmentActionRenameToTableRecruitmentTask extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_action', function (Blueprint $table) {
            Schema::rename('tbl_recruitment_action', 'tbl_recruitment_task');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_action', function (Blueprint $table) {
            Schema::rename('tbl_recruitment_task', 'tbl_recruitment_action');
        });
    }

}
