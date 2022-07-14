<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYesReasonInTblNeedAssessmentCommunication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_need_assessment_communication', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_need_assessment_communication', 'yes_verbal_instruction')) {
                $table->unsignedInteger('yes_verbal_instruction')->nullable()->after("instructions_desc")->default(0)->comment('1-Single Word, 2-Short Sentences, 3-Full Sentence ');
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
        Schema::table('tbl_need_assessment_communication', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_need_assessment_communication', 'yes_verbal_instruction')) {
                $table->dropColumn('yes_verbal_instruction');
            }
        });
    }
}
