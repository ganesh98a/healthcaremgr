<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFmsCaseAgainstDetailAddComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_fms_case_against_detail', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_fms_case_against_detail', 'against_by')) {
                $table->unsignedInteger('against_by')->comment('Id of Member, Participant, ORG, ONCALL User/Admin, Site')->change();
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
        Schema::dropIfExists('tbl_fms_case_against_detail');
    }
}
