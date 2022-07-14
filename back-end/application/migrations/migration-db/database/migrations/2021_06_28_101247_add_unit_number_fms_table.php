<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUnitNumberFmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_fms_feedback_location', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_fms_feedback_location', 'unit_number')) {
                $table->string('unit_number',255)->nullable()->after("caseId")->comments('Apartment/Unit number');
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
        Schema::table('tbl_fms_feedback_location', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_fms_feedback_location', 'unit_number')) {
                $table->dropColumn('unit_number');
            }
        });
    }
}
