<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnsArchiveCrmStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_staff', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_crm_staff', 'approval_permission') && !Schema::hasColumn('tbl_crm_staff', 'its_crm_admin') && !Schema::hasColumn('tbl_crm_staff', 'archive')) {
                $table->unsignedTinyInteger('approval_permission')->nullable()->comment('0 - No, 1 - Yes');
                $table->unsignedTinyInteger('its_crm_admin')->nullable()->comment('0 - No, 1 - Yes');
                $table->unsignedTinyInteger('archive')->default(0)->comment('0 - No, 1 - Yes');
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
        Schema::table('tbl_crm_staff', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_crm_staff', 'approval_permission') && Schema::hasColumn('tbl_crm_staff', 'its_crm_admin') && Schema::hasColumn('tbl_crm_staff', 'archive')) {
                $table->dropColumn('approval_permission');
                $table->dropColumn('its_crm_admin');
                $table->dropColumn('archive');
            }
        });
    }
}
