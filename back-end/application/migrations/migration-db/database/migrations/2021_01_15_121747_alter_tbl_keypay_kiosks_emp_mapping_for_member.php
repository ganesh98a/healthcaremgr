<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblKeypayKiosksEmpMappingForMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_keypay_kiosks_emp_mapping_for_member', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_keypay_kiosks_emp_mapping_for_member', 'member_id')) {
                $table->foreign('member_id')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            }
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_keypay_kiosks_emp_mapping_for_member', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_keypay_kiosks_emp_mapping_for_member', 'member_id')) {
                $table->dropForeign(['member_id']);
            }
            if (Schema::hasColumn('tbl_keypay_kiosks_emp_mapping_for_member', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
            if (Schema::hasColumn('tbl_keypay_kiosks_emp_mapping_for_member', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
}
