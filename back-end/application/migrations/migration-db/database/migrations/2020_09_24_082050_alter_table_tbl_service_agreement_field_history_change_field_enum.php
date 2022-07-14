<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;

class AlterTableTblServiceAgreementFieldHistoryChangeFieldEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_service_agreement_field_history')) {
            Schema::table('tbl_service_agreement_field_history', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_service_agreement_field_history', 'field')) {
                    DB::statement("ALTER TABLE `tbl_service_agreement_field_history` CHANGE `field` `field` ENUM('owner','status','grand_total','sub_total','tax','additional_services','additional_services_custom','customer_signed_date','contract_start_date','contract_end_date','plan_start_date','plan_end_date','signed_by','created_by','goals', 'created') NOT NULL");
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_service_agreement_field_history')) {
            Schema::table('tbl_service_agreement_field_history', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_service_agreement_field_history', 'field')) {
                    DB::statement("ALTER TABLE `tbl_service_agreement_field_history` CHANGE `field` `field` ENUM('owner','status','grand_total','sub_total','tax','additional_services','additional_services_custom','customer_signed_date','contract_start_date','contract_end_date','plan_start_date','plan_end_date','signed_by','created_by','goals') NOT NULL");
                }
            });
        }
    }
}
