<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCampaignDetailsTblRecruitmentApplicantAppliedApplication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_applicant_applied_application', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'campaign_source')) {
                $table->string('campaign_source', 255)->nullable()->comment('Source FB/Google')->after('referrer_url');
            }
            if (!Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'ad_name')) {
                $table->string('ad_name', 255)->nullable()->comment('Ad name given by Marketers')->after('campaign_source');
            }
            if (!Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'campaign')) {
                $table->string('campaign', 255)->nullable()->comment('Campaign name given by Marketers')->after('ad_name');
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
            if (Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'campaign_source')) {
                $table->dropColumn('campaign_source');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'ad_name')) {
                $table->dropColumn('ad_name');
            }
            if (Schema::hasColumn('tbl_recruitment_applicant_applied_application', 'campaign')) {
                $table->dropColumn('campaign');
            }
        });
    }
}
