<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentStageLabelAsAddColumnStageOrderByDisplayStageNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('tbl_recruitment_stage_label', function (Blueprint $table) {
        if (!Schema::hasColumn('tbl_recruitment_stage_label', 'stage_order_by')) {
          $table->unsignedInteger('stage_order_by')->comment('used for sorting')->after('stage_number');
        }

        if (!Schema::hasColumn('tbl_recruitment_stage_label', 'display_stage_number')) {
          $table->string('display_stage_number',100)->comment('used to display stage in front side')->after('stage_order_by');
        }
      });

      $seeder = new RecruitmentStage();
      $seeder->run();
      
      $seeder_2 = new RecruitmentStageLabel();
      $seeder_2->run();

      $seeder_3 = new RecruitmentTaskStage();
      $seeder_3->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('tbl_recruitment_stage_label', function (Blueprint $table) {
        if (Schema::hasColumn('tbl_recruitment_stage_label', 'stage_order_by')) {
          $table->dropColumn('stage_order_by');
        }
        if (Schema::hasColumn('tbl_recruitment_stage_label', 'display_stage_number')) {
          $table->dropColumn('display_stage_number');
        }
      });
      
    }
  }
