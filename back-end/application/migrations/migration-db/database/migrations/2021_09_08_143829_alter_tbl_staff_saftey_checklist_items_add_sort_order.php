<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblStaffSafteyChecklistItemsAddSortOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_opportunity_staff_saftey_checklist')) {
            Schema::table('tbl_opportunity_staff_saftey_checklist', function (Blueprint $table) {
                $table->unsignedBigInteger('opportunity_id')->nullable()->change();
            });
        }
        if (Schema::hasTable('tbl_staff_saftey_checklist_items')) {
            Schema::table('tbl_staff_saftey_checklist_items', function (Blueprint $table) {
                $table->smallInteger('sort_order')->nullable()->default(0)->comment('sorting order');
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
        Schema::table('tbl_staff_saftey_checklist_items', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_staff_saftey_checklist_items', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
}
