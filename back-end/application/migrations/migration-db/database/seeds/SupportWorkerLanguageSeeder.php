<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class SupportWorkerLanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $langs = DB::table('tbl_references')->select('display_name')->where(['type' => 14, 'archive' => 0])->pluck('display_name')->all();
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_reference_worker_support_language.json");
        $queryData = (array) json_decode($json, true);
        foreach ($queryData as $obj) {
            if (in_array($obj['display_name'], $langs)) {
                continue;
            }
            DB::table('tbl_references')->updateOrInsert($obj);
        }
    }
}
