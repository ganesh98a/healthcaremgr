<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class AlterRunRecruitmentStageLabelSeederForAddNewColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_stage_label', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_stage_label', 'label_used_in_create_job')) {
                $table->string('label_used_in_create_job',200)->after('used_in_create_job');
            }
        });

        // $seeder = new RecruitmentStageLabel();
        // $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
     
        Schema::table('tbl_recruitment_stage_label', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_stage_label', 'used_in_create_job')) {
                $table->dropColumn('used_in_create_job');
            }

            if (Schema::hasColumn('tbl_recruitment_stage_label', 'key_name')) {
                $table->dropColumn('key_name');
            }
        });
        
    }
}
