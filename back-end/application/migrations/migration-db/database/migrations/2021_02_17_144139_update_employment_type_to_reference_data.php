<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateEmploymentTypeToReferenceData extends Migration
{
    public function up()
    {    
        
            $items = DB::select("Select rj.id as job_id,rjet.id,rjet.title,rj.employment_type from tbl_recruitment_job rj join tbl_recruitment_job_employment_type rjet on rj.employment_type = rjet.id");
            
            foreach($items as $value) { 
                  $reference_id  = DB::select("Select r.id as ref_id , r.display_name from tbl_reference_data_type rd join tbl_references r on rd.id = r.type where rd.key_name='employment_type' and r.display_name = '".$value->title."'");                  

                  if(!empty($reference_id)){
                    DB::table('tbl_recruitment_job')
                    ->where('id',$value->job_id)
                    ->update(["employment_type" => $reference_id[0]->ref_id]);
                  }
                
            
            }
    }
}
