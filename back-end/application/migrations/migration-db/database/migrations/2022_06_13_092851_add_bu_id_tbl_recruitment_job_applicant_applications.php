<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBuIdTblRecruitmentJobApplicantApplications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('tbl_recruitment_job', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_job', 'bu_id')) {
                $table->unsignedInteger('bu_id')->nullable()->comment('business unit id')->after('title');
                $table->foreign('bu_id')->references('id')->on('tbl_business_units');
            }

        });

        Schema::table('tbl_recruitment_applicant', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant', 'bu_id')) {
                $table->unsignedInteger('bu_id')->nullable()->comment('business unit id')->after('uuid');
                $table->foreign('bu_id')->references('id')->on('tbl_business_units');
            }

        });

        Schema::table('tbl_recruitment_applicant_applied_application', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'bu_id')) {
                $table->unsignedInteger('bu_id')->nullable()->comment('business unit id')->after('applicant_id');
                $table->foreign('bu_id')->references('id')->on('tbl_business_units');
            }

        });

        $table_data = ['tbl_recruitment_job', 'tbl_recruitment_applicant', 'tbl_recruitment_applicant_applied_application'];

        //Adding bu id for old data's
        foreach($table_data as $tab){
            $row = DB::select("Select id from $tab order by id asc");

            if(!empty($row)){
                foreach($row as $data) {
                   
                    DB::table($tab)
                    ->where('id', $data->id)
                    ->update([
                        "bu_id" => 1
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
