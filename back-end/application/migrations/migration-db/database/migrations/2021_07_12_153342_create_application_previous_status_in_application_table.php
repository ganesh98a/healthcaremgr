<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicationPreviousStatusInApplicationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_applied_application', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'prev_application_process_status')) {
                $table->unsignedInteger('prev_application_process_status')->default('0')->comment('relating application prev status')->after('rejected_date');
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
        Schema::table('tbl_recruitment_applicant_applied_application', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'prev_application_process_status')) {
                 $table->dropColumn('prev_application_process_status');
            }
        });
    }
}
