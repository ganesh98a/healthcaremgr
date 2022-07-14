<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractIdInServiceAgreementAttachmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        $upsql = "UPDATE `tbl_service_agreement_attachment` SET is_contract_id_added=1 , contract_id =  CONCAT((SUBSTRING('DS000000000', 1, 11-CHAR_LENGTH(id))) ,id)
        where CHAR_LENGTH(contract_id)<10";
        $up = DB::update($upsql);
        if (!$up) {
        echo "sql failed \n";
        } else {
         echo "migrated row=> $up \n";
        }
        DB::commit();                 
    } 
       
}

