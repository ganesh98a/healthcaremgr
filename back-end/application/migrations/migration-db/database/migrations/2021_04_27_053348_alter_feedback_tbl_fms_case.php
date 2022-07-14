<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFeedbackTblFmsCase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_fms_case')) {
            Schema::table('tbl_fms_case', function (Blueprint $table) {
                Schema::rename('tbl_fms_case', 'tbl_fms_feedback');
            });
        }

        if (Schema::hasTable('tbl_fms_case_against_detail')) {
            Schema::table('tbl_fms_case_against_detail', function (Blueprint $table) {
                Schema::rename('tbl_fms_case_against_detail', 'tbl_fms_feedback_against_detail');
            });
        }

        if (Schema::hasTable('tbl_fms_case_category')) {
            Schema::table('tbl_fms_case_category', function (Blueprint $table) {
                Schema::rename('tbl_fms_case_category', 'tbl_fms_feedback_category');
            });
        }

        if (Schema::hasTable('tbl_fms_case_department')) {
            Schema::table('tbl_fms_case_department', function (Blueprint $table) {
                Schema::rename('tbl_fms_case_department', 'tbl_fms_feedback_department');
            });
        }

        if (Schema::hasTable('tbl_fms_case_location')) {
            Schema::table('tbl_fms_case_location', function (Blueprint $table) {
                Schema::rename('tbl_fms_case_location', 'tbl_fms_feedback_location');
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
        Schema::dropIfExists('tbl_fms_feeback');
        Schema::dropIfExists('tbl_fms_feedback_against_detail');
        Schema::dropIfExists('tbl_fms_feedback_category');
        Schema::dropIfExists('tbl_fms_feedback_department');
    }
}
