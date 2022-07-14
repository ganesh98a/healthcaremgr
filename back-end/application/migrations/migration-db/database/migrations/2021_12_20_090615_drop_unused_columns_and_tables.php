<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropUnusedColumnsAndTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('tbl_participant_ability_requirements');
        Schema::dropIfExists('tbl_participant_plan_site');
        Schema::dropIfExists('tbl_participant_remove_account');

        Schema::table('tbl_member', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member', 'prefer_contact')) {
                 $table->dropColumn('prefer_contact');
            }
            if (Schema::hasColumn('tbl_member', 'username_bk_29042020')) {
                $table->dropColumn('username_bk_29042020');
           }
        });
        Schema::table('tbl_person', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_person', 'password')) {
                 $table->dropColumn('password');
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
        Schema::dropIfExists('tbl_participant_remove_account');
        $createTableSql =  "CREATE TABLE `tbl_participant_remove_account` (
            `id` int(10) UNSIGNED NOT NULL,
            `participantId` int(10) UNSIGNED NOT NULL DEFAULT 0,
            `reason` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
            `contact` tinyint(3) UNSIGNED NOT NULL COMMENT '1- Yes, 0- No'
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
           DB::statement($createTableSql);
           $sql1="ALTER TABLE `tbl_participant_remove_account`  ADD PRIMARY KEY (`id`),ADD KEY `tbl_participant_remove_account_participantid_index` (`participantId`)";
           DB::statement($sql1);
           $sql2="ALTER TABLE `tbl_participant_remove_account`
           MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
           DB::statement($sql2);

        Schema::dropIfExists('tbl_participant_plan_site');
        $createTableSql =  "CREATE TABLE `tbl_participant_plan_site` (
            `planId` int(10) UNSIGNED NOT NULL DEFAULT 0,
            `participantId` int(10) UNSIGNED NOT NULL,
            `address` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
            `city` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
            `postal` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
            `state` tinyint(3) UNSIGNED NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            DB::statement($createTableSql);
            $sql1="ALTER TABLE `tbl_participant_plan_site`
            ADD KEY `tbl_participant_plan_site_planid_index` (`planId`),
            ADD KEY `tbl_participant_plan_site_participantid_index` (`participantId`)";
            DB::statement($sql1);

            Schema::dropIfExists('tbl_participant_ability_requirements');
        $createTableSql =  "CREATE TABLE `tbl_participant_ability_requirements` (
                `id` int(10) UNSIGNED NOT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        DB::statement($createTableSql);
        $sql1="ALTER TABLE `tbl_participant_ability_requirements` ADD PRIMARY KEY (`id`)";
        DB::statement($sql1);
        $sql2="ALTER TABLE `tbl_participant_ability_requirements`MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
        DB::statement($sql2);

        Schema::table('tbl_member', function (Blueprint $table) {
           if (!Schema::hasColumn('tbl_member', 'username_bk_29042020')) {
                $table->string('username_bk_29042020',64)->nullable()->comment('Backup username (29 Apr 2020)');
            } 
            if (!Schema::hasColumn('tbl_member', 'prefer_contact')) {
                $table->string('prefer_contact', 6);
            }
        });

        Schema::table('tbl_person', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_person', 'password')) {
                $table->string('password',255)->nullable();
            }
        });
    }
}
