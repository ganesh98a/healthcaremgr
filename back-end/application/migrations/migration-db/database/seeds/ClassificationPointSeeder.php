<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class ClassificationPointSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_classification_point.json");
        $queryData = (array) json_decode($json, true);
        DB::table('tbl_classification_point')->truncate();
        foreach ($queryData as $obj) {
            DB::table('tbl_classification_point')->updateOrInsert(['id' => $obj['id']], $obj);
        }
    }

}
