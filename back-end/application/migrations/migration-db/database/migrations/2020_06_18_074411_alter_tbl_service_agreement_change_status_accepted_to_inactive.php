<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblServiceAgreementChangeStatusAcceptedToInactive extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_service_agreement', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_service_agreement', 'status')) {
                $table->unsignedSmallInteger('status')->comment('0- Draft, 1- Awaiting Approval, 3- Inactive, 4- Declined, 5- Active')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_service_agreement', function (Blueprint $table) {
            
        });
    }

}
