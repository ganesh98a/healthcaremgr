<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblNeedAssementOwnerForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_need_assessment', function (Blueprint $table) {
            DB::unprepared("ALTER TABLE `tbl_need_assessment` DROP FOREIGN KEY `tbl_need_assessment_owner_foreign`; ALTER TABLE `tbl_need_assessment` 
            ADD CONSTRAINT `tbl_need_assessment_owner_foreign` FOREIGN KEY (`owner`) REFERENCES `tbl_users`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT");
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
