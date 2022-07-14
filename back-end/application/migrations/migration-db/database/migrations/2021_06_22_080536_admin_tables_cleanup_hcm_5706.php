<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdminTablesCleanupHcm5706 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('create_tbl_dummy');
        Schema::dropIfExists('pythontest');
        Schema::dropIfExists('tbl_admin');
        Schema::dropIfExists('tbl_admin_email');
        Schema::dropIfExists('tbl_admin_email_old');
        Schema::dropIfExists('tbl_admin_login');
        Schema::dropIfExists('tbl_admin_login_old');
        Schema::dropIfExists('tbl_admin_old');
        Schema::dropIfExists('tbl_admin_phone');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $createTableSql = "
        CREATE TABLE IF NOT EXISTS `create_tbl_dummy` (
          `id` int(10) UNSIGNED NOT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL
        )";
        DB::statement($createTableSql);
        $indexSql1 = "ALTER TABLE `create_tbl_dummy` ADD PRIMARY KEY (`id`)";
        DB::statement($indexSql1);
        $indexSql2 = "ALTER TABLE `create_tbl_dummy` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
        DB::statement($indexSql2);

        // table pythontest
        $sql = "CREATE TABLE `pythontest` (
          `id` int(3) DEFAULT NULL,
          `participant_email` varchar(16) DEFAULT NULL,
          `participant_id` int(1) DEFAULT NULL,
          `invoice_number` varchar(13) DEFAULT NULL,
          `invoice_date` varchar(10) DEFAULT NULL,
          `due_date` varchar(10) DEFAULT NULL,
          `company_name` varchar(35) DEFAULT NULL,
          `account_number` varchar(10) DEFAULT NULL,
          `invoice_amount` int(1) DEFAULT NULL,
          `biller_code` int(5) DEFAULT NULL,
          `reference_no` varchar(1) DEFAULT NULL,
          `po_number` varchar(10) DEFAULT NULL,
          `gst_value` int(1) DEFAULT NULL,
          `status` int(1) DEFAULT NULL,
          `read_status` int(1) DEFAULT NULL,
          `created` varchar(19) DEFAULT NULL,
          `updated` varchar(19) DEFAULT NULL,
          `pdf_url` varchar(141) DEFAULT NULL,
          `html_url` varchar(250) DEFAULT NULL,
          `original_id` varchar(3) DEFAULT NULL,
          `payment_type` int(1) DEFAULT NULL,
          `load_status` int(1) DEFAULT NULL,
          `xero_invoice_id` varchar(36) DEFAULT NULL,
          `account_name` varchar(10) DEFAULT NULL,
          `payment_method` int(1) DEFAULT NULL
        )";
        DB::statement($sql);
        //
        $sql1 = "CREATE TABLE `tbl_admin` (
          `id` int(10) UNSIGNED NOT NULL,
          `companyId` int(10) UNSIGNED NOT NULL DEFAULT 0,
          `username` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
          `password` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
          `pin` text COLLATE utf8mb4_unicode_ci NOT NULL,
          `firstname` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
          `lastname` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
          `profile` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
          `position` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
          `department` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
          `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '1- Active, 0- Inactive',
          `background` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
          `gender` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '1- Male, 2- Female',
          `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
          `archive` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0- not archive, 1- archive data',
          `created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
        )";
        $sql2 = "ALTER TABLE `tbl_admin`
        ADD PRIMARY KEY (`id`),
        ADD KEY `tbl_admin_companyid_index` (`companyId`),
        ADD KEY `tbl_admin_username_index` (`username`),
        ADD KEY `tbl_admin_firstname_index` (`firstname`),
        ADD KEY `tbl_admin_lastname_index` (`lastname`),
        ADD KEY `tbl_admin_status_index` (`status`)";
        $sql3 = "ALTER TABLE `tbl_admin` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
        DB::statement($sql1);
        DB::statement($sql2);
        DB::statement($sql3);
        //
        $sql1 = "CREATE TABLE `tbl_admin_email` (
          `id` int(10) UNSIGNED NOT NULL,
          `adminId` int(10) UNSIGNED NOT NULL,
          `email` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
          `primary_email` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '1- Primary, 2- Secondary',
          `archive` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0- not archive, 1- archive data(delete)'
        )";
        $sql2 = "ALTER TABLE `tbl_admin_email` ADD PRIMARY KEY (`id`)";
        $sql3 = "ALTER TABLE `tbl_admin_email` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
        DB::statement($sql1);
        DB::statement($sql2);
        DB::statement($sql3);
        //
        $sql1 = "CREATE TABLE `tbl_admin_login` (
          `id` int(10) UNSIGNED NOT NULL,
          `adminId` int(10) UNSIGNED NOT NULL,
          `ip_address` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
          `token` text COLLATE utf8mb4_unicode_ci NOT NULL,
          `created` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          `pin` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
        )";
        
        $sql2 = "ALTER TABLE `tbl_admin_login` ADD PRIMARY KEY (`id`)";
        
        $sql3 = "ALTER TABLE `tbl_admin_login` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
        DB::statement($sql1);
        DB::statement($sql2);
        DB::statement($sql3);
        //
        $sql1 = "CREATE TABLE `tbl_admin_phone` (
          `id` int(10) UNSIGNED NOT NULL,
          `adminId` int(10) UNSIGNED NOT NULL,
          `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
          `primary_phone` tinyint(3) UNSIGNED NOT NULL COMMENT '1- Primary, 2- Secondary',
          `archive` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0- not archive, 1- archive data(delete)'
        )";
        
        $sql2 = "ALTER TABLE `tbl_admin_phone` ADD PRIMARY KEY (`id`)";
        
        $sql3 = "ALTER TABLE `tbl_admin_phone` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
        DB::statement($sql1);
        DB::statement($sql2);
        DB::statement($sql3);
    }
}
