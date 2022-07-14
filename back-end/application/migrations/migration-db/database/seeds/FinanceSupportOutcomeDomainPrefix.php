<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class FinanceSupportOutcomeDomainPrefix extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $json = File::get(Config::get('constants.JSON_FILE_PATH') . "tbl_finance_support_outcome_domain_prefix.json");
        $queryData = (array) json_decode($json, true);
        foreach ($queryData as $obj) {
            $where = array('id' => $obj['id']);
            $update = array('name' => $obj['name'], 'prefix' => $obj['prefix']);
             DB::statement("UPDATE `tbl_finance_support_outcome_domain` SET `name` = '".$obj['name']."', prefix = '".$obj['prefix']."' WHERE id = '".$obj['id']."' ");
        }
    }
}
