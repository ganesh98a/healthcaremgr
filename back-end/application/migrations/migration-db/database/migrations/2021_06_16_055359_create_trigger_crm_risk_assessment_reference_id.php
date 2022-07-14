<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTriggerCrmRiskAssessmentReferenceID extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        if (Schema::hasTable('tbl_crm_risk_assessment')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_crm_risk_assessment_reference_id_after_id`');
            DB::unprepared("CREATE TRIGGER `tbl_crm_risk_assessment_reference_id_after_id` BEFORE INSERT ON `tbl_crm_risk_assessment` FOR EACH ROW
                IF NEW.reference_id IS NULL or NEW.reference_id=''  THEN 
                SET NEW.reference_id=  (SELECT CONCAT('RA',(select LPAD(d.autoid_data,8,0)  from (select sum(Coalesce((SELECT id FROM tbl_crm_risk_assessment ORDER BY id DESC LIMIT 1),0)+ 1) as autoid_data) as d)));
                END IF;");
        }

    }

    public function down(){
        if (Schema::hasTable('tbl_crm_risk_assessment')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_crm_risk_assessment_reference_id_after_id`');
        }

    }
   
}