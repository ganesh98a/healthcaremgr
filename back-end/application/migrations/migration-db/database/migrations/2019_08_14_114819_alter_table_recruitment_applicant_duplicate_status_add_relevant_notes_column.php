<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableRecruitmentApplicantDuplicateStatusAddRelevantNotesColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      
        if (Schema::hasTable('tbl_recruitment_applicant_duplicate_status')) {
            Schema::table('tbl_recruitment_applicant_duplicate_status', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_recruitment_applicant_duplicate_status','relevant_note')){
                    $table->longText('relevant_note')->nullable()->comment('duplicate relevant notes');
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
        if (Schema::hasTable('tbl_recruitment_applicant_duplicate_status')) {
            Schema::table('tbl_recruitment_applicant_duplicate_status', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_recruitment_applicant_duplicate_status','relevant_note')){
                    $table->dropColumn('relevant_note');
                } 
            });

        }
    }
}
