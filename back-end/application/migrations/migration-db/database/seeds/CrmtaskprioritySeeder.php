<?php


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class CrmtaskprioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
          $json = File::get(Config::get('constants.JSON_FILE_PATH')."tbl_crm_task_priority.json");
          $queryData = (array) json_decode($json, true);
          foreach ($queryData as $obj) {
              DB::table('tbl_crm_task_priority')->updateOrInsert(['id' => $obj['id']],
              $obj);
          }
      }
}
