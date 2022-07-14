<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class FinanceCostBookMapping extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_finance_cost_book_mapping.json");
        $queryData = (array) json_decode($json, true);
        foreach ($queryData as $obj) {
            $cost_code_tit = $obj['cost_code'];
            $service_area_tit = $obj['service_area'];
            $cost_code = DB::select("select id from tbl_finance_cost_code WHERE title = '".$cost_code_tit."'");
            $cost_code_id = $cost_code[0]->id ?? '';

            $service_area = DB::select("select id from tbl_finance_service_area WHERE title = '".$service_area_tit."'");
            $service_area_id = $service_area[0]->id ?? '';

            $obj['cost_code_id'] = $cost_code_id;
            $obj['service_area_id'] = $service_area_id;
            unset($obj['cost_code'], $obj['service_area']);
            DB::table('tbl_finance_cost_book_mapping')->updateOrInsert($obj, $obj);
        }
    }

        /**
     * Reverse the database seeds.
     *
     * @return void
     */
    public function rollback() {
        DB::delete("delete from tbl_finance_cost_book_mapping where id != 0");
    }

}
