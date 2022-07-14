<?php


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class PlanManagementBillerSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
      $json = File::get(Config::get('constants.JSON_FILE_PATH')."tbl_plan_management_biller.json");
      $queryData = (array) json_decode($json, true);
      foreach ($queryData as $obj) {
          DB::table('tbl_plan_management_biller')->updateOrInsert(['id' => $obj['id']],
          $obj);
      }
  }
}
