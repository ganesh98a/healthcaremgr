<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblServiceAgreementAttachmentAddContractIdSeed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_service_agreement_attachment', function (Blueprint $table) {
            //add a field in tbl_service_agreement_goal to check successful migration
            if (!Schema::hasColumn('tbl_service_agreement_attachment', 'is_contract_id_added')) {
                $table->unsignedInteger('is_contract_id_added')->default(0)->comment('track if contract_id is created for existing records');
            }
        });
        Schema::table('tbl_service_agreement_attachment', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_service_agreement_attachment', 'contract_id')) {
                $table->string('contract_id')->default(null)->comment("Auto generated id with reference to id")->after("id");
            }            
        });
        $dbseeder = new ContractIdInServiceAgreementAttachmentSeeder();
        $dbseeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_service_agreement_attachment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'is_contract_id_added')) {
                $table->dropColumn('is_contract_id_added');
            }
        });
        Schema::table('tbl_service_agreement_attachment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'contract_id')) {
                $table->dropColumn('contract_id');
            }  
        });
    }
}
