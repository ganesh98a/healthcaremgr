<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblServiceAgreementAsAddColumnServiceAgreementId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        Schema::table('tbl_service_agreement', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_service_agreement', 'service_agreement_id')) {
                $table->string('service_agreement_id',255)->comment("Auto generated id with reference to id")->after("id");
            }
        });


        if (Schema::hasTable('tbl_service_agreement')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `service_agreement_id_after_id`');
            DB::unprepared("CREATE TRIGGER `service_agreement_id_after_id` BEFORE INSERT ON `tbl_service_agreement` FOR EACH ROW
                IF NEW.service_agreement_id IS NULL or NEW.service_agreement_id='' THEN
                SET NEW.service_agreement_id = (SELECT CONCAT('SA',(select LPAD(d.autoid_data,8,0)  from (select sum(Coalesce((SELECT id FROM tbl_service_agreement ORDER BY id DESC LIMIT 1),0)+ 1) as autoid_data) as d)));
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
        Schema::table('tbl_service_agreement', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_service_agreement', 'service_agreement_id')) {
                $table->dropColumn('service_agreement_id');
            }
        });
    }
}
