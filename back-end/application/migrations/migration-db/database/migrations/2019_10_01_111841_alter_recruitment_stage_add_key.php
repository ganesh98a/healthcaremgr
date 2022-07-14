<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentStageAddKey extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_recruitment_stage', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_stage', 'stage_label_id')) {
                $table->string('stage_key', 200)->after('stage_label_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_recruitment_stage', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_stage', 'stage_key')) {
                $table->dropColumn('stage_key');
            }
        });
    }

}
