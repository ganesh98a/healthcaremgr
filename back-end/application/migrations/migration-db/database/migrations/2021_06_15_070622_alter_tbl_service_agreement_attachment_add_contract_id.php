<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblServiceAgreementAttachmentAddContractId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        Schema::table('tbl_service_agreement_attachment', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_service_agreement_attachment', 'contract_id')) {
                $table->string('contract_id',255)->comment("Auto generated id with reference to id")->after("id");
            }
        });


        if (Schema::hasTable('tbl_service_agreement_attachment')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_service_agreement_attachment_contract_id_after_id`');
            DB::unprepared("CREATE TRIGGER `tbl_service_agreement_attachment_contract_id_after_id` BEFORE INSERT ON `tbl_service_agreement_attachment` FOR EACH ROW
                IF NEW.contract_id IS NULL or NEW.contract_id=''  THEN 
                SET NEW.contract_id=(SELECT CONCAT('DS',(select LPAD(d.autoid_data,9,0)  from (select sum(Coalesce((SELECT id FROM tbl_service_agreement_attachment ORDER BY id DESC LIMIT 1),0)+ 1) as autoid_data) as d)));
                END IF;");
        }

     
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_service_agreement_attachment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'contract_id')) {
                $table->dropColumn('contract_id');
            }
        });
    }
}
