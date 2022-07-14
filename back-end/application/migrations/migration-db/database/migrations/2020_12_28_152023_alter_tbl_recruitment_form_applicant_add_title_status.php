<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentFormApplicantAddTitleStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_form_applicant', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_form_applicant', 'title')) {
                $table->text('title')->nullable()->after('form_id');
            }
            if (!Schema::hasColumn('tbl_recruitment_form_applicant', 'owner')) {
                $table->unsignedInteger('owner')->nullable()->after('title')->comment('reference of tbl_member.id');
                 $table->foreign('owner')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            }
            if (!Schema::hasColumn('tbl_recruitment_form_applicant', 'status')) {
                $table->unsignedInteger('status')->nullable()->comment('1 - Draft / 2 - Completed')->after('owner');
            }
            if (!Schema::hasColumn('tbl_recruitment_form_applicant', 'created_by')) {
                $table->unsignedInteger('created_by')->nullable()->after('status')->comment('reference of tbl_member.id');
                 $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
            }
            if (!Schema::hasColumn('tbl_recruitment_form_applicant', 'updated_by')) {
                $table->unsignedInteger('updated_by')->nullable()->after('status')->comment('reference of tbl_member.id');
                 $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('tbl_recruitment_form_applicant', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_form_applicant', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('tbl_recruitment_form_applicant', 'owner')) {
                $table->dropForeign(['owner']);
                $table->dropColumn('owner');
            }
            if (Schema::hasColumn('tbl_recruitment_form_applicant', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('tbl_recruitment_form_applicant', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('tbl_recruitment_form_applicant', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
        });
    }
}
