<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class DocumentTypeDSWSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_document_type_add_dsw.json");
    	$queryData = (array) json_decode($json, true);
    	foreach ($queryData as $obj) {
            $typeObje = [];
            $typeObje['title'] =  $obj['title'];
            $typeObje['issue_date_mandatory'] =  $obj['issue_date_mandatory'];
            $typeObje['expire_date_mandatory'] =  $obj['expire_date_mandatory'];
            $typeObje['reference_number_mandatory'] =  $obj['reference_number_mandatory'];
            $typeObje['active'] =  $obj['active'];
            $typeObje['system_gen_flag'] =  $obj['system_gen_flag'];
            $typeObje['doc_category_id'] =  $obj['doc_category_id'];
            $typeObje['archive'] =  $obj['archive'];
            $typeObje['created_at'] =  $obj['created_at'];

    		$docTypeId = DB::table('tbl_document_type')->insertGetId($typeObje);
            $relatedData = $obj['related'];
            foreach ($relatedData as $rel_obj) {
                $relObj = [];
                $relObj['doc_type_id'] =  $docTypeId;
                $relObj['related_to'] =  $rel_obj['related_to'];
                $relObj['archive'] =  $rel_obj['archive'];
                $relObj['created_at'] =  $rel_obj['created_at'];
                DB::table('tbl_document_type_related')->insertGetId($relObj);
            }
    	}
    }
}
