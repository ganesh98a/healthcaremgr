<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOAEntityTypeTblViewedLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_viewed_log', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_viewed_log', 'entity_type')) {

                $table->unsignedInteger('entity_type')
                    ->comment('1 - Application / 2 - Applicant / 3 - Leads / 4 - Opportunity / 5 - Service Agreement/ 6 - Online Assessment')->change();
                }
            if (Schema::hasColumn('tbl_viewed_log', 'entity_id')) {

                $table->unsignedInteger('entity_id')
                    ->comment('reference of (entity_type = 1 - tbl_recruitment_applicant_applied_application / 2 - tbl_recruitment_applicant / 3 - tbl_leads / 4 - tbl_opportunity / 5 - tbl_service_agreement).id 6 - tbl_recruitment_oa_template.id')->change();
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
        Schema::table('tbl_viewed_log', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_viewed_log', 'entity_type')) {
                $table->dropIndex(['entity_type']);

            }
            if (Schema::hasColumn('tbl_viewed_log', 'entity_id')) {
                $table->dropIndex(['entity_id']);

            }
        });
    }
}
