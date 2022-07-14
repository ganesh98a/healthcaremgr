<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentFormApplicantAddIsSystemGenerator extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_form_applicant', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_form_applicant', 'is_sys_generater')) {
                $table->unsignedSmallInteger('is_sys_generater')->default(0)->comment("0- No, 1- Yes");               
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
            if (Schema::hasColumn('tbl_shift', 'is_sys_generater')) {
                $table->dropColumn('is_sys_generater');
           }
        });
    }
}
