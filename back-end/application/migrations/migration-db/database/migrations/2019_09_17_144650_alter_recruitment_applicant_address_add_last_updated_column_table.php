<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentApplicantAddressAddLastUpdatedColumnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
		Schema::table('tbl_recruitment_applicant_address', function (Blueprint $table) {
			if (!Schema::hasColumn('tbl_recruitment_applicant_address','updated')) {
				$table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->after('created');
			}
		});
		
             /*if (Schema::hasTable('tbl_recruitment_applicant_address')) {
				Schema::table('tbl_recruitment_applicant_address', function (Blueprint $table) {
					if (!Schema::hasColumn('tbl_recruitment_applicant_address','updated')) {
						$table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
					}
				}
			} */
		
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_recruitment_applicant_address', function (Blueprint $table) {
			if (Schema::hasColumn('tbl_recruitment_applicant_address','updated')) {
						$table->dropColumn('updated');
					}
		});
            
	}
}
