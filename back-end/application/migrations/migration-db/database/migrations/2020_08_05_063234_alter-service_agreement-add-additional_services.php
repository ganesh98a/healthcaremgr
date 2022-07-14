<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterServiceAgreementAddAdditionalServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_service_agreement', function (Blueprint $table) {
            $table->string('additional_services_custom', 200)->after('tax')->nullable(); 
            $table->string('additional_services', 200)->after('tax')->nullable(); // 'after' will resolve in reverse           
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_service_agreement', function (Blueprint $table) {
            $table->dropColumn('additional_services');
            $table->dropColumn('additional_services_custom');
        });
    }
}
