<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblRecruitmentFlagReasonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_recruitment_flag_reason')) {
            Schema::create('tbl_recruitment_flag_reason', function (Blueprint $table) {
                $table->increments('id');
                $table->text('reason_title')->nullable();
                $table->dateTime('created')->nullable();
            });
        }

        if (Schema::hasTable('tbl_recruitment_flag_applicant')) {
            Schema::table('tbl_recruitment_flag_applicant', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_flag_applicant', 'reason_id')) {
                    $table->unsignedSmallInteger('reason_id')->default(0)->comment("tbl_recruitment_flag_reason.id table")->after('applicant_id');
                }
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
        Schema::dropIfExists('tbl_recruitment_flag_reason');

        if (Schema::hasTable('tbl_recruitment_flag_applicant')) {
            Schema::table('tbl_recruitment_flag_applicant', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_flag_applicant', 'reason_id')) {
                    $table->dropColumn('reason_id');
                }
            });
        }
    }
}
