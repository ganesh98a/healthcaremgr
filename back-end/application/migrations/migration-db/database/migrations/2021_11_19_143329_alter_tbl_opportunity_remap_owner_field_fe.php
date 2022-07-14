<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOpportunityRemapOwnerFieldFe extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_opportunity', function (Blueprint $table) {
            DB::unprepared("ALTER TABLE `tbl_opportunity` DROP FOREIGN KEY `tbl_opportunity_owner_foreign`; 
            ALTER TABLE `tbl_opportunity` ADD CONSTRAINT `tbl_opportunity_owner_foreign` FOREIGN KEY (`owner`) 
            REFERENCES `tbl_users`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;");
        });
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
