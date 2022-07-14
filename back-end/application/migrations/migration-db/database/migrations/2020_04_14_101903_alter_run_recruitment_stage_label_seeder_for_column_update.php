<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class AlterRunRecruitmentStageLabelSeederForColumnUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_stage_label', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_stage_label', 'key_name')) {
                $table->string('key_name',200)->after('title');
            }

            if (!Schema::hasColumn('tbl_recruitment_stage_label', 'used_in_create_job')) {
                $table->unsignedSmallInteger('used_in_create_job')->default(0)->comment("1 used in dropdown when create job")->after('stage_number');
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
