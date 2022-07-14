<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCostBookReferenceValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $list_cost_book_ref = DB::select("SELECT r.display_name , r.id as ref_id , r.type FROM `tbl_reference_data_type` as rdt inner join `tbl_references` as r on rdt.id=r.type where rdt.key_name='cost_book' and rdt.archive=0");

        foreach($list_cost_book_ref as $value) {    
            if(!empty($value)){
                // replace underscore
                $cost_book_value = str_replace(array("-"), '', $value->display_name);
                    if (strpos($cost_book_value, 'One Site') !== false) {
                        $cost_book_value = str_replace(array("One Site"), '', $cost_book_value);
                        $cost_book_value = str_replace('  ', ' ', $cost_book_value);
                        DB::table('tbl_references')
                        ->where(['id' => $value->ref_id])
                        ->update(["display_name" => $cost_book_value]);
                    }else{
                        DB::table('tbl_references')
                        ->where(['id' => $value->ref_id])
                        ->update(["display_name" => $cost_book_value]);
                    }
                
            }                
        }

        $seeder = new CostBookReferenceSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
