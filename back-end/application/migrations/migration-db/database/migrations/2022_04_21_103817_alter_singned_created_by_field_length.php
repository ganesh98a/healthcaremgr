<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSingnedCreatedByFieldLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_service_agreement_attachment')) {
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'created_by')) {
                DB::statement("ALTER TABLE `tbl_service_agreement_attachment` CHANGE `created_by` `created_by` INT(10) UNSIGNED NULL DEFAULT NULL;");
            }
            if (Schema::hasColumn('tbl_service_agreement_attachment', 'signed_by')) {
                DB::statement("ALTER TABLE `tbl_service_agreement_attachment` CHANGE `signed_by` `signed_by` INT(10) UNSIGNED NULL DEFAULT NULL;");
            }
        }       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
