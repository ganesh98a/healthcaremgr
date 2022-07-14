<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmParticipantRearrangeAddColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_participant', function (Blueprint $table) {   
			if (Schema::hasColumn('tbl_crm_participant', 'referral_org')) {
                $table->dropColumn('referral_org');
            }
			if (Schema::hasColumn('tbl_crm_participant', 'referral_relation')) {
                $table->dropColumn('referral_relation');
            }			
			if (Schema::hasColumn('tbl_crm_participant', 'referral_phone')) {
                $table->dropColumn('referral_phone');
            }
			
			if(Schema::hasColumn('tbl_crm_participant', 'fomal_diagnosis_primary')) {				
				$table->dropColumn('fomal_diagnosis_primary');
			}
			if (Schema::hasColumn('tbl_crm_participant', 'fomal_diagnosis_secondary')) {				
				$table->dropColumn('fomal_diagnosis_secondary');
			}
			if (Schema::hasColumn('tbl_crm_participant', 'other_relevant_details')) {				
				$table->dropColumn('other_relevant_details');
			}
			if (Schema::hasColumn('tbl_crm_participant', 'disability_legal_issues')) {				
				$table->dropColumn('disability_legal_issues');
			}			
			
			if (Schema::hasColumn('tbl_crm_participant', 'address')) {				
				$table->dropColumn('address');
			}
			if (Schema::hasColumn('tbl_crm_participant', 'state')) {			
				$table->dropColumn('state');
			}
			if (Schema::hasColumn('tbl_crm_participant', 'postcode')) {				
				$table->dropColumn('postcode');
			}
			
			
		     
		});
		
		Schema::table('tbl_crm_participant', function (Blueprint $table) {  
			if(!Schema::hasColumn('tbl_crm_participant', 'referral_phone')){
				$table->string('referral_phone',20)->after('referral_email');
			}
			if(!Schema::hasColumn('tbl_crm_participant', 'referral_org')){
				$table->string('referral_org',30)->nullable()->after('referral_email');
			}
			if(!Schema::hasColumn('tbl_crm_participant', 'referral_relation')){
				$table->string('referral_relation',30)->nullable()->after('referral_email');
			}
			
			if(!Schema::hasColumn('tbl_crm_participant', 'crn')) {
				$table->string('crn',20)->nullable()->after('medicare_num');
			}
			
			if (!Schema::hasColumn('tbl_crm_participant', 'cognitive')) {
				$table->unsignedInteger('cognitive')->after('ndis_plan');
			}
			if (!Schema::hasColumn('tbl_crm_participant', 'communication')) {
				$table->unsignedInteger('communication')->after('cognitive');
			}
			if (!Schema::hasColumn('tbl_crm_participant', 'hearing_interpreter')) {
				$table->unsignedInteger('hearing_interpreter')->after('communication')->comment('0 for No/1 for Yes');;
			}
			if (!Schema::hasColumn('tbl_crm_participant', 'linguistically_background')) {
				$table->unsignedInteger('linguistically_background')->after('hearing_interpreter')->comment('0 for No/1 for Yes');;
			}
			if (!Schema::hasColumn('tbl_crm_participant', 'language_interpreter')) {
				$table->unsignedInteger('language_interpreter')->after('linguistically_background')->comment('0 for No/1 for Yes');
			}
			if (!Schema::hasColumn('tbl_crm_participant', 'language_spoken')) {
				$table->string('language_spoken',500)->after('language_interpreter')->comment('0 for No/1 for Yes');
			}
			
			if (!Schema::hasColumn('tbl_crm_participant', 'carers_gender')) {
				$table->unsignedInteger('carers_gender')->after('language_spoken')->comment('1- Male, 2- Female');
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
        Schema::table('tbl_crm_participant', function (Blueprint $table) {
            //
        });
    }
}
