<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftAddSupportType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'service_agreement_id')) {
                // $table->dropForeign(['service_agreement_id']);
                // $table->dropColumn('service_agreement_id');
            }
            if (!Schema::hasColumn('tbl_shift', 'support_type')) {
                $table->unsignedInteger('support_type')->nullable()->after("roster_id")->comment('tbl_finance_support_type.id');
                $table->foreign('support_type')->references('id')->on('tbl_finance_support_type')->onUpdate('cascade')->onDelete('cascade');
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
            if (Schema::hasColumn('tbl_shift', 'support_type')) {
                $table->dropForeign(['support_type']);
                $table->dropColumn('support_type');
            }
        });
    }
}
