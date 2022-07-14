<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddManualAddressApplicantContact extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_address', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant_address', 'is_manual_address')) {
                $table->unsignedSmallInteger('is_manual_address')->default(0)->comment("0- No, 1- Yes");               
            }
            if (!Schema::hasColumn('tbl_recruitment_applicant_address', 'manual_address')) {
                $table->text('manual_address')->nullable();             
            }           
          
        });

        Schema::table('tbl_person_address', function (Blueprint $table) {           
            if (!Schema::hasColumn('tbl_person_address', 'is_manual_address')) {
                $table->unsignedSmallInteger('is_manual_address')->default(0)->comment("0- No, 1- Yes");               
            }
            if (!Schema::hasColumn('tbl_person_address', 'manual_address')) {
                $table->text('manual_address')->nullable();             
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
        Schema::table('tbl_recruitment_applicant_address', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_applicant_address', 'is_manual_address')) {
                $table->dropColumn('is_manual_address');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant_address', 'manual_address')) {
                $table->dropColumn('manual_address');
            }            
        });
        Schema::table('tbl_person_address', function (Blueprint $table) {            
            if (Schema::hasColumn('tbl_person_address', 'is_manual_address')) {
                $table->dropColumn('is_manual_address');
            }
            if (Schema::hasColumn('tbl_person_address', 'manual_address')) {
                $table->dropColumn('manual_address');
            }
        });
    }
}
