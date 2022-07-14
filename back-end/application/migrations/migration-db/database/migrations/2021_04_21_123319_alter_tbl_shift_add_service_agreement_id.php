<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftAddServiceAgreementId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift', 'service_agreement_id')) {
                $table->unsignedInteger('service_agreement_id')->nullable()->after("roster_id")->comment('tbl_service_agreement.id');
                $table->foreign('service_agreement_id')->references('id')->on('tbl_service_agreement')->onUpdate('cascade')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'service_agreement_id')) {
                $table->dropForeign(['service_agreement_id']);
                $table->dropColumn('service_agreement_id');
            }
        });
    }
}
