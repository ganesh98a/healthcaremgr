<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMemberAddApplicantIdAndSourceType extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_member', function (Blueprint $table) {
            $table->unsignedInteger('applicant_id')->default('0')->comment('primary key tbl_recruitment_applicant');
            $table->unsignedInteger('source_type')->default('0')->comment('0 - HCM/1 - recruitment portal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_member', function (Blueprint $table) {
            $table->dropColumn('applicant_id');
            $table->dropColumn('source_type');
        });
    }

}
