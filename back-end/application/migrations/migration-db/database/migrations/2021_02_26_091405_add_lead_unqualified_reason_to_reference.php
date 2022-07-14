<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLeadUnqualifiedReasonToReference extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
         $references  = DB::select("SELECT r.id as ref_id FROM `tbl_reference_data_type` as rdt inner join `tbl_references` as r on rdt.id=r.type where rdt.key_name = 'unqualified_reason_lead' and rdt.archive=0 ");                  
         foreach($references as $val) {
            if(!empty($val->ref_id)){
                DB::table('tbl_references')
                ->where("id",$val->ref_id)
                ->update(["archive" => 1]);
            }
        }
        // add the unqualified reason reference data in ref table
        $seeder = new UnqualifiedLeadRefData();
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
