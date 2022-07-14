<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentInterviewTypeAddKeyTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_interview_type')) {
            Schema::table('tbl_recruitment_interview_type', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_interview_type','key_type')) {
                    $table->string('key_type',50)->nullable()->comment('it is unique key data fetch for backend when option label name change');
                }
                //
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
        if (Schema::hasTable('tbl_recruitment_interview_type')) {
            Schema::table('tbl_recruitment_interview_type', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_interview_type','key_type')) {
                    $table->dropColumn('key_type');
                }
            });
        }
    }
}
