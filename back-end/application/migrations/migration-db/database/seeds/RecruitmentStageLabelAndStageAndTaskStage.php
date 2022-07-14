<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class RecruitmentStageLabelAndStageAndTaskStage extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        /* $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_recruitment_combination_stage_and_task_stage_and_stage_label.json");
          $queryData = (array) json_decode($json, true);
          foreach ($queryData as $obj) {
          $stage_label_id = 0;

          if (!empty($obj->tbl_recruitment_stage_label)) {
          DB::table('tbl_recruitment_task_stage')->updateOrInsert(['id' => $obj['id']], $obj);
          }
          print_r($obj->tbl_recruitment_stage_label);

          // DB::table('tbl_recruitment_task_stage')->updateOrInsert(['id' => $obj['id']], $obj);
          } */
    }

}
