<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOpportunityStatusAddFinalStageAndOrder extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_opportunity_status', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_opportunity_status', 'its_final_stage')) {
                $table->unsignedSmallInteger('its_final_stage')->default('0')->comment("0 -Not / 1 - Yes");
            }

            if (!Schema::hasColumn('tbl_opportunity_status', 'order_status')) {
                $table->unsignedInteger('order_status')->default('0')->comment("order to show");
            }

        
        });
        
            $seeder = new OpportunityStatusSeeder();
            $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_opportunity_status', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_opportunity_status', 'its_final_stage')) {
                $table->dropColumn('its_final_stage');
            }

            if (Schema::hasColumn('tbl_opportunity_status', 'order_status')) {
                $table->dropColumn('order_status');
            }
        });
    }

}
