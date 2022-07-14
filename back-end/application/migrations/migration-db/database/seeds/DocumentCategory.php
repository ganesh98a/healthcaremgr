<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class DocumentCategory extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_document_category.json");
        $queryData = (array) json_decode($json, true);
        foreach ($queryData as $obj) {
        	$obj['created_at'] = date('Y-m-d h:i:s');
            DB::table('tbl_document_category')->updateOrInsert(['id' => $obj['id']], $obj);
        }
    }
}
