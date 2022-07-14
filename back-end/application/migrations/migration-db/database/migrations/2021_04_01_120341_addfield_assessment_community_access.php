<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddfieldAssessmentCommunityAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_need_assessment_community_access')) {
            Schema::table('tbl_need_assessment_community_access', function (Blueprint $table) {

                if (!Schema::hasColumn('tbl_need_assessment_community_access', 'toileting')) {
                    $table->unsignedSmallInteger('toileting')->comment("2- with assistance, 3- with supervision, 4- independant")->after('not_applicable');
                }
                if (!Schema::hasColumn('tbl_need_assessment_community_access', 'organiz_admin')) {
                    $table->unsignedSmallInteger('organiz_admin')->comment("2- with assistance, 3- with supervision, 4- independant")->after('toileting');
                }
                if (!Schema::hasColumn('tbl_need_assessment_community_access', 'bank_money')) {
                    $table->unsignedSmallInteger('bank_money')->comment("2- with assistance, 3- with supervision, 4- independant")->after('organiz_admin');
                }
                if (!Schema::hasColumn('tbl_need_assessment_community_access', 'community_access')) {
                    $table->unsignedSmallInteger('community_access')->comment("2- with assistance, 3- with supervision, 4- independant")->after('bank_money');
                }
                if (!Schema::hasColumn('tbl_need_assessment_community_access', 'navigate_trans'))
                {
                    $table->unsignedSmallInteger('navigate_trans')->comment("2- with assistance, 3- with supervision, 4- independant")->after('community_access');
                }

                if (Schema::hasColumn('tbl_need_assessment_community_access', 'method_transport'))
                {
                    $table->unsignedSmallInteger('method_transport')->comment("0- Not applicable, 1- Public transport, 2- Support worker vehicle, 3- No paid transport, 4- Rideshare")->change('community_access');
                }

                DB::statement("UPDATE `tbl_assessment_assistance` SET `archive` = 1 where key_name = 'community_access'");
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

    }
}
