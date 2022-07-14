<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnApplicationIdToAttachmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
            if ( ! Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'application_id')) {
                $table->unsignedInteger('application_id')->after('applicant_id')->default(0)->comment('tbl_recruitment_applicant_applied_application.id');
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
        Schema::table('tbl_recruitment_applicant_stage_attachment', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_stage_attachment', 'application_id')) {
                $table->dropColumn('application_id');
            }
        });
    }
}
