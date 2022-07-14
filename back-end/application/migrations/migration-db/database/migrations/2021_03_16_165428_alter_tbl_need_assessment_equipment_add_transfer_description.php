<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblNeedAssessmentEquipmentAddTransferDescription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_need_assessment_equipment', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_need_assessment_equipment', 'transfer_aides_description')) {
                $table->mediumText('transfer_aides_description')->nullable()->after('transfer_aides');
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
        Schema::table('tbl_need_assessment_equipment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_need_assessment_equipment', 'transfer_aides_description')) {
                $table->dropColumn('transfer_aides_description');
            }
        });
    }
}
