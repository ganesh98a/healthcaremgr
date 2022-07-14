<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class DropSalesModuleUnusedTablesPart2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
         // drop the table
         Schema::dropIfExists('tbl_crm_participant_line_items');
         Schema::dropIfExists('tbl_crm_participant_phone');
         Schema::dropIfExists('tbl_crm_participant_plan');
         Schema::dropIfExists('tbl_crm_participant_plan_breakdown');
         Schema::dropIfExists('tbl_crm_participant_roster');
         Schema::dropIfExists('tbl_crm_participant_roster_data');
         Schema::dropIfExists('tbl_crm_participant_schedule_task_docs');
         Schema::dropIfExists('tbl_crm_participant_shift');
         Schema::dropIfExists('tbl_crm_participant_shifts');
         Schema::dropIfExists('tbl_shift_crm_participant');
         Schema::dropIfExists('tbl_opportunity_contact');
    }
      /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    $createTableSql =  "CREATE TABLE `tbl_crm_participant_line_items` (
        `id` int(10) UNSIGNED NOT NULL,
        `crm_plan_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'tbl_crm_participant_plan auto increment id',
        `line_item_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'tbl_finance_line_item auto increment id',
        `amount` double(14,2) NOT NULL,
        `updated` datetime NOT NULL DEFAULT current_timestamp(),
        `created` timestamp NOT NULL DEFAULT current_timestamp(),
        `archive` tinyint(3) UNSIGNED NOT NULL COMMENT '0- not /1 - archive'
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
       DB::statement($createTableSql);
       $sql1="ALTER TABLE `tbl_crm_participant_line_items` ADD PRIMARY KEY (`id`)";
       DB::statement($sql1);
       $sql2="ALTER TABLE `tbl_crm_participant_line_items` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
       DB::statement($sql2);

    $createTableSql =  "CREATE TABLE `tbl_crm_participant_phone` (
        `id` int(10) UNSIGNED NOT NULL,
        `crm_participant_id` int(10) UNSIGNED NOT NULL,
        `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
        `primary_phone` tinyint(3) UNSIGNED NOT NULL COMMENT '1- Primary, 2- Secondary'
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
      DB::statement($createTableSql);
      $sql1="ALTER TABLE `tbl_crm_participant_phone` ADD PRIMARY KEY (`id`), ADD KEY `tbl_crm_participant_phone_crm_participant_id_index` (`crm_participant_id`)";
      DB::statement($sql1);
      $sql2="ALTER TABLE `tbl_crm_participant_phone`  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
      DB::statement($sql2);

    $createTableSql =  "CREATE TABLE `tbl_crm_participant_plan` (
       `id` int(10) UNSIGNED NOT NULL,
       `crm_participant_id` int(10) UNSIGNED NOT NULL,
       `plan_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
       `plan_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
       `start_date` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
       `end_date` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
       `total_funding` double(10,2) NOT NULL DEFAULT 0.00,
       `fund_used` double(10,2) NOT NULL DEFAULT 0.00,
       `remaing_fund` double(10,2) NOT NULL DEFAULT 0.00,
       `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0- past,1 - ongoing',
       `archive` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0- not archive, 1- archive data'
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
      DB::statement($createTableSql);
      $sql1="ALTER TABLE `tbl_crm_participant_plan` ADD PRIMARY KEY (`id`),ADD KEY `tbl_crm_participant_plan_crm_participant_id_index` (`crm_participant_id`)";
      DB::statement($sql1);
      $sql2="ALTER TABLE `tbl_crm_participant_plan` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
      DB::statement($sql2);

    $createTableSql = "CREATE TABLE `tbl_crm_participant_plan_breakdown` (
        `id` int(10) UNSIGNED NOT NULL,
        `crm_participant_id` int(10) UNSIGNED NOT NULL,
        `support_item_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        `support_item_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
        `amount` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
        `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
        `updated` timestamp NOT NULL DEFAULT current_timestamp(),
        `plan_id` int(11) NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
      DB::statement($createTableSql);
      $sql1="ALTER TABLE `tbl_crm_participant_plan_breakdown` ADD PRIMARY KEY (`id`)";
      DB::statement($sql1);
      $sql2="ALTER TABLE `tbl_crm_participant_plan_breakdown` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
      DB::statement($sql2);

     $createTableSql = "CREATE TABLE `tbl_crm_participant_roster` (
        `id` int(10) UNSIGNED NOT NULL,
        `participantId` int(10) UNSIGNED NOT NULL,
        `start_date` timestamp NULL DEFAULT NULL,
        `status` tinyint(3) UNSIGNED NOT NULL COMMENT '1- Inactive, 2- Active',
        `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
        `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        `shift_round` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
        `is_default` tinyint(3) UNSIGNED NOT NULL COMMENT '1- No, 0- Yes',
        `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        `end_date` timestamp NULL DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
      DB::statement($createTableSql);
      $sql1="ALTER TABLE `tbl_crm_participant_roster` ADD PRIMARY KEY (`id`)";
      DB::statement($sql1);
      $sql2="ALTER TABLE `tbl_crm_participant_roster` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
      DB::statement($sql2);

     $createTableSql = " CREATE TABLE `tbl_crm_participant_roster_data` (
        `id` int(10) UNSIGNED NOT NULL,
        `rosterId` int(10) UNSIGNED NOT NULL,
        `week_day` tinyint(3) UNSIGNED NOT NULL COMMENT '1- Mon, 2- Tue... 7-Sun',
        `start_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
        `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
        `week_number` tinyint(3) UNSIGNED NOT NULL COMMENT '1- First, 2- second, 3- Third, 4- Four'
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
   
     DB::statement($createTableSql);
     $sql1="ALTER TABLE `tbl_crm_participant_roster_data` ADD PRIMARY KEY (`id`), ADD KEY `tbl_crm_participant_roster_data_rosterid_index` (`rosterId`)";
     DB::statement($sql1);
     $sql2="ALTER TABLE `tbl_crm_participant_roster_data` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
     DB::statement($sql2);

    $createTableSql = "CREATE TABLE `tbl_crm_participant_schedule_task_docs` (
    `id` int(10) UNSIGNED NOT NULL,
    `crm_task_id` int(10) UNSIGNED NOT NULL,
    `documents` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `created` timestamp NOT NULL DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
   DB::statement($createTableSql);
   $sql1="ALTER TABLE `tbl_crm_participant_schedule_task_docs`ADD PRIMARY KEY (`id`)";
   DB::statement($sql1);
   $sql2="ALTER TABLE `tbl_crm_participant_schedule_task_docs` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
   DB::statement($sql2);

    $createTableSql = " CREATE TABLE `tbl_crm_participant_shift` (
    `id` int(10) UNSIGNED NOT NULL,
    `crmRosterId` int(10) UNSIGNED NOT NULL,
    `day` tinyint(3) UNSIGNED NOT NULL COMMENT '1- Mon, 2- Tue, 3- Wed, 4- Thu, 5- Fri,6- Sat,7- Sun',
    `shift_type` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '1- Am, 2- Pm,3- Sleep-over,4- Atlive night',
    `archived` tinyint(3) UNSIGNED NOT NULL COMMENT '1- Yes, 0- No',
    `created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
   DB::statement($createTableSql);
   $sql1="ALTER TABLE `tbl_crm_participant_shift` ADD PRIMARY KEY (`id`)";
   DB::statement($sql1);
   $sql2="ALTER TABLE `tbl_crm_participant_shift` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
   DB::statement($sql2);

  $createTableSql = "CREATE TABLE `tbl_crm_participant_shifts` (
    `id` int(10) UNSIGNED NOT NULL,
    `crm_participant_id` int(10) UNSIGNED NOT NULL,
    `status` tinyint(3) UNSIGNED NOT NULL COMMENT '1- Active / 2 - Archive',
    `created` timestamp NOT NULL DEFAULT current_timestamp(),
    `shift_date` timestamp NULL DEFAULT NULL,
    `start_time` timestamp NULL DEFAULT NULL,
    `end_time` timestamp NULL DEFAULT NULL,
    `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `rosterId` int(10) UNSIGNED NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
  DB::statement($createTableSql);
  $sql1="ALTER TABLE `tbl_crm_participant_shifts` ADD PRIMARY KEY (`id`),ADD KEY `tbl_crm_participant_shifts_crm_participant_id_index` (`crm_participant_id`)";
  DB::statement($sql1);
  $sql2="ALTER TABLE `tbl_crm_participant_shifts` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
  DB::statement($sql2);
    
  $createTableSql = "CREATE TABLE `tbl_opportunity_contact` (
    `id` int(10) UNSIGNED NOT NULL,
    `opportunity_id` int(10) UNSIGNED NOT NULL,
    `contact_id` int(10) UNSIGNED NOT NULL,
    `roll_id` int(10) UNSIGNED NOT NULL,
    `is_primary` tinyint(3) UNSIGNED NOT NULL COMMENT '0-No/1-Yes',
    `created` timestamp NOT NULL DEFAULT current_timestamp(),
    `created_by` int(10) UNSIGNED NOT NULL,
    `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `updated_by` int(10) UNSIGNED NOT NULL,
    `archive` smallint(5) UNSIGNED NOT NULL COMMENT '0-No/1-Yes'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    DB::statement($createTableSql);
    $sql1="ALTER TABLE `tbl_opportunity_contact` ADD PRIMARY KEY (`id`)";
    DB::statement($sql1);
    $sql2="ALTER TABLE `tbl_opportunity_contact` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
    DB::statement($sql2);

    $createTableSql = "CREATE TABLE `tbl_shift_crm_participant` (
    `id` int(10) UNSIGNED NOT NULL,
    `participantId` int(10) UNSIGNED NOT NULL,
    `status` tinyint(3) UNSIGNED NOT NULL,
    `created` timestamp NOT NULL DEFAULT current_timestamp()
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    DB::statement($createTableSql);
    $sql1=" ALTER TABLE `tbl_shift_crm_participant` ADD PRIMARY KEY (`id`), ADD KEY `tbl_shift_crm_participant_participantid_index` (`participantId`)";
    DB::statement($sql1);
    $sql2="ALTER TABLE `tbl_shift_crm_participant` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
    DB::statement($sql2);
}
}