<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblApplicationHistoryAddStatusFromGroupBooking extends Migration
{
      /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_application_field_history', function (Blueprint $table) {
            if (Schema::hasTable('tbl_application_field_history')) {
                if (Schema::hasColumn('tbl_application_field_history', 'field')) {
                    DB::statement("ALTER TABLE `tbl_application_field_history` CHANGE `field` `field` ENUM('owner', 'status', 'created_by', 'job_transfer','status_updated_from_gb') NOT NULL");
                }
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
        Schema::table('tbl_application_field_history', function (Blueprint $table) {
            if (Schema::hasTable('tbl_application_field_history')) {
                if (Schema::hasColumn('tbl_application_field_history', 'field')) {
                    DB::statement("ALTER TABLE `tbl_application_field_history` CHANGE `field` `field` ENUM('owner', 'status', 'created_by','job_transfer') NOT NULL");
                }
            }
        });
    }
}
