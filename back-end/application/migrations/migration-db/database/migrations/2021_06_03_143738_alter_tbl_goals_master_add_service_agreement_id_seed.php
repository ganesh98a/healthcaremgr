<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblGoalsMasterAddServiceAgreementIdSeed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_service_agreement_goal', function (Blueprint $table) {
            //add a field in tbl_service_agreement_goal to check successful migration
            if (!Schema::hasColumn('tbl_service_agreement_goal', 'is_migrated')) {
                $table->unsignedInteger('is_migrated')->default(0)->comment('track if sa goal is migrated to participant goal');
            }
        });
        Schema::table('tbl_goals_master', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_goals_master', 'service_agreement_id')) {
                $table->unsignedInteger('service_agreement_id')->after('participant_master_id')->nullable()->default(null)->comment('tbl_service_agreement.id');
                $table->foreign('service_agreement_id')->references('id')->on('tbl_service_agreement')->onUpdate("cascade")->onDelete("cascade");                
            }
            if (!Schema::hasColumn('tbl_goals_master', 'service_type')) {
                $table->string('service_type')->nullable()->default(null)->comment('tbl_opportunity.topic');
            }            
        });
        $dbseeder = new ServiceAgreementGoalsToGoalsMaster();
        $dbseeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_service_agreement_goal', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_service_agreement_goal', 'is_migrated')) {
                $table->dropColumn('is_migrated');
            }
        });
        Schema::table('tbl_goals_master', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_goals_master', 'service_agreement_id')) {
                $table->dropForeign(['service_agreement_id']);
                $table->dropColumn('service_agreement_id');
            }
            if (Schema::hasColumn('tbl_goals_master', 'service_type')) {
                $table->dropColumn('service_type');
            }  
        });
    }
}
