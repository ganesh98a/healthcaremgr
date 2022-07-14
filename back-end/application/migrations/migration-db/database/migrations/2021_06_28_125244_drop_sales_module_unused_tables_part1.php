<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class DropSalesModuleUnusedTablesPart1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
         // drop the table
       Schema::dropIfExists('tbl_crm_logs');
       Schema::dropIfExists('tbl_crm_ndis_plan');
       Schema::dropIfExists('tbl_crm_participant_ability');
       Schema::dropIfExists('tbl_crm_participant_ability_requirements');
       Schema::dropIfExists('tbl_crm_participant_address');
       Schema::dropIfExists('tbl_crm_participant_booking_list');
       Schema::dropIfExists('tbl_crm_participant_care_not_to_book');
       Schema::dropIfExists('tbl_crm_participant_disability');
       Schema::dropIfExists('tbl_crm_participant_docs');
       Schema::dropIfExists('tbl_crm_participant_email');
       Schema::dropIfExists('tbl_crm_participant_kin');
    }
      /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      $createTableSql =  "CREATE TABLE `tbl_crm_logs` (
        `id` int(10) UNSIGNED NOT NULL,
        `companyId` smallint(5) UNSIGNED DEFAULT NULL,
        `userId` int(10) UNSIGNED DEFAULT NULL,
        `module` int(10) UNSIGNED DEFAULT NULL COMMENT '1- Admin/ 2- Participant / 3 - Member/ 4 - Schedule / 5 - FSM / 6 - House / 7 - Organization / 8 - Imail / 9 - Recruitment / 10 - CRMAdmin',
        `sub_module` int(10) UNSIGNED DEFAULT NULL COMMENT 'if schedule (1 - Shift / 2 - Roster) , if Imail (1 - Externam mail/ 2 - Internal Mail) ',
        `title` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `created` timestamp NULL DEFAULT current_timestamp(),
        `created_by` int(10) UNSIGNED DEFAULT 0,
        `created_type` tinyint(3) UNSIGNED NOT NULL COMMENT '1 - admin / 2 - participant'
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
       DB::statement($createTableSql);
       $sql1="ALTER TABLE `tbl_crm_logs` ADD PRIMARY KEY (`id`), ADD KEY `companyId` (`companyId`), ADD KEY `user_member_id` (`userId`),ADD KEY `module` (`module`)";
       DB::statement($sql1);
       $sql2="ALTER TABLE `tbl_crm_logs` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
       DB::statement($sql2);

      $createTableSql ="CREATE TABLE `tbl_crm_ndis_plan` (
        `id` int(10) UNSIGNED NOT NULL,
        `crm_participant_id` int(10) UNSIGNED NOT NULL,
        `manager_plan` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
        `manager_email` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
        `manager_address` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
        `state` tinyint(3) UNSIGNED NOT NULL,
        `post_code` int(10) UNSIGNED NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
       DB::statement($createTableSql);
       $sql1="ALTER TABLE `tbl_crm_ndis_plan`  ADD PRIMARY KEY (`id`)";
       DB::statement($sql1);
       $sql2="ALTER TABLE `tbl_crm_ndis_plan` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
       DB::statement($sql2);
    
    $createTableSql = "CREATE TABLE `tbl_crm_participant_ability` (
      `id` int(10) UNSIGNED NOT NULL,
      `crm_participant_id` int(10) UNSIGNED NOT NULL,
      `cognitive_level` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
      `communication` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
      `hearing_interpreter` tinyint(3) UNSIGNED DEFAULT 1 COMMENT '1- Yes, 0- No',
      `language_interpreter` tinyint(3) UNSIGNED DEFAULT 1 COMMENT '1- Yes, 0- No',
      `languages_spoken` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
      `require_assistance` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
      `require_mobility` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
      `linguistic_diverse` tinyint(3) UNSIGNED DEFAULT 1 COMMENT '1- Yes, 0- No',
      `docs` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
      `languages_spoken_other` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
      `require_assistance_other` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
      `require_mobility_other` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
     DB::statement($createTableSql);
     $sql1="ALTER TABLE `tbl_crm_participant_ability` ADD PRIMARY KEY (`id`)";
     DB::statement($sql1);
     $sql2="ALTER TABLE `tbl_crm_participant_ability` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
     DB::statement($sql2);
    
    $createTableSql = "CREATE TABLE `tbl_crm_participant_ability_requirements` (
      `id` int(10) UNSIGNED NOT NULL,
      `crm_participant_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'tbl_crm_participant_ability_requirements auto increment id',
      `requirment` int(10) UNSIGNED NOT NULL,
      `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
      `status` tinyint(3) UNSIGNED NOT NULL COMMENT '0-for inactive/ 1-for-active',
      `created` timestamp NOT NULL DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
     DB::statement($createTableSql);
     $sql1="ALTER TABLE `tbl_crm_participant_ability_requirements` ADD PRIMARY KEY (`id`)";
     DB::statement($sql1);
     $sql2="ALTER TABLE `tbl_crm_participant_ability_requirements` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
     DB::statement($sql2);

    $createTableSql = "CREATE TABLE `tbl_crm_participant_address` (
      `id` int(10) UNSIGNED NOT NULL,
      `crm_participant_id` int(10) UNSIGNED NOT NULL,
      `street` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
      `city` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
      `postal` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
      `state` tinyint(3) UNSIGNED NOT NULL,
      `address_type` tinyint(3) UNSIGNED NOT NULL COMMENT '1-Own Home,2-Family Home , 3 -Mum''s House, 4- Dad''s House , 5- Relative''s House, 6- Friend''s House, 7 - OnCall House',
      `lat` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
      `long` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
      `site_category` tinyint(3) UNSIGNED NOT NULL,
      `primary_address` tinyint(3) UNSIGNED NOT NULL COMMENT '1- Primary, 2- Secondary',
      `archive` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0- not archive, 1- archive data(delete)'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
     DB::statement($createTableSql);
     $sql1="ALTER TABLE `tbl_crm_participant_address`ADD PRIMARY KEY (`id`),ADD KEY `tbl_crm_participant_address_crm_participant_id_index` (`crm_participant_id`)";
     DB::statement($sql1);
     $sql2="ALTER TABLE `tbl_crm_participant_address` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
     DB::statement($sql2);

    $createTableSql = "CREATE TABLE `tbl_crm_participant_booking_list` (
      `id` int(10) UNSIGNED NOT NULL,
      `crm_participant_id` int(10) UNSIGNED NOT NULL,
      `firstname` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
      `lastname` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
      `relation` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
      `phone` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
      `email` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
      `primary_booker` smallint(5) UNSIGNED NOT NULL COMMENT '1- Primary/2-Secondary',
      `created` datetime NOT NULL,
      `updated` timestamp NOT NULL DEFAULT current_timestamp(),
      `archive` tinyint(3) UNSIGNED NOT NULL COMMENT '0- not /1 - archive'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
     DB::statement($createTableSql);
     $sql1="ALTER TABLE `tbl_crm_participant_booking_list` ADD PRIMARY KEY (`id`)";
     DB::statement($sql1);
     $sql2="ALTER TABLE `tbl_crm_participant_booking_list` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
     DB::statement($sql2);

    $createTableSql = "CREATE TABLE `tbl_crm_participant_disability` (
        `id` int(10) UNSIGNED NOT NULL,
        `crm_participant_id` int(10) UNSIGNED DEFAULT NULL,
        `primary_fomal_diagnosis_desc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '1- Primary, 2- Secondary',
        `secondary_fomal_diagnosis_desc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `other_relevant_information` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `legal_issues` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `linked_fms_case_id` int(10) UNSIGNED DEFAULT NULL,
        `status` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `docs` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
      DB::statement($createTableSql);
      $sql1="ALTER TABLE `tbl_crm_participant_disability` ADD PRIMARY KEY (`id`)";
      DB::statement($sql1);
      $sql2="ALTER TABLE `tbl_crm_participant_disability` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
      DB::statement($sql2);

      
      $createTableSql = "CREATE TABLE `tbl_crm_participant_docs` (
        `id` int(10) UNSIGNED NOT NULL,
        `crm_participant_id` int(10) UNSIGNED NOT NULL,
        `stage_id` int(11) NOT NULL,
        `type` int(11) NOT NULL,
        `doc_category` int(10) UNSIGNED NOT NULL COMMENT '0- Intake Plan Docs, 1- NDIS Plan Docs, 2- Behavioral Support Plan Docs, 3- Manage Attachments Docs',
        `title` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
        `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `created` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        `resend_docusign` timestamp NOT NULL DEFAULT current_timestamp(),
        `document_type` int(11) NOT NULL DEFAULT 0 COMMENT '1=service agreement 2=funding concent 3=final service agreement',
        `document_signed` int(11) NOT NULL DEFAULT 0 COMMENT '0=No 2=Yes',
        `envelope_id` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
        `signed_file_path` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
        `archive` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0- not archive, 1- archive data(delete)',
        `docu_signed_page_number` int(10) UNSIGNED DEFAULT 0,
        `behavioral_support_doc` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `other_relevent_doc` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `legal_isues_doc` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `notes` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `is_old_doc` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0-New Document, 1-Old Document'
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
       DB::statement($createTableSql);
       $sql1="ALTER TABLE `tbl_crm_participant_docs` ADD PRIMARY KEY (`id`), ADD KEY `tbl_crm_participant_docs_crm_participant_id_index` (`crm_participant_id`),
       ADD KEY `tbl_crm_participant_docs_type_index` (`type`)";
       DB::statement($sql1);
       $sql2="ALTER TABLE `tbl_crm_participant_docs` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
       DB::statement($sql2);
    
       $createTableSql = "CREATE TABLE `tbl_crm_participant_email` (
        `id` int(10) UNSIGNED NOT NULL,
        `crm_participant_id` int(10) UNSIGNED NOT NULL,
        `email` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
        `primary_email` tinyint(3) UNSIGNED NOT NULL COMMENT '1- Primary, 2- Secondary'
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
      DB::statement($createTableSql);
      $sql1="ALTER TABLE `tbl_crm_participant_email` ADD PRIMARY KEY (`id`),ADD KEY `tbl_crm_participant_email_crm_participant_id_index` (`crm_participant_id`)";
      DB::statement($sql1);
      $sql2="ALTER TABLE `tbl_crm_participant_email` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";
      DB::statement($sql2);

      
  $createTableSql = "CREATE TABLE `tbl_crm_participant_care_not_to_book` (
    `crm_participant_id` int(10) UNSIGNED NOT NULL COMMENT 'primary key of table `tbl_crm_participant`',
    `carer_type` int(10) UNSIGNED NOT NULL COMMENT 'primary key of table `tbl_ethnicity`,`tbl_religious_beliefs`',
    `type` smallint(5) UNSIGNED NOT NULL COMMENT '1 for ethnicity/2 for religious',
    `archive` smallint(5) UNSIGNED NOT NULL COMMENT '0 for No/1 for Yes'
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
  DB::statement($createTableSql);

  $createTableSql = "CREATE TABLE `tbl_crm_participant_kin` (
    `id` int(10) UNSIGNED NOT NULL,
    `crm_participant_id` int(10) UNSIGNED NOT NULL,
    `firstname` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
    `lastname` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
    `relation` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
    `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
    `email` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
    `primary_kin` tinyint(3) UNSIGNED NOT NULL COMMENT '1- Primary, 2- Secondary',
    `updated` timestamp NOT NULL DEFAULT current_timestamp()
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
   DB::statement($createTableSql);
   $sql1="ALTER TABLE `tbl_crm_participant_kin` ADD PRIMARY KEY (`id`),ADD KEY `tbl_crm_participant_kin_crm_participant_id_index` (`crm_participant_id`)";
   DB::statement($sql1);
   $sql2="ALTER TABLE `tbl_crm_participant_kin` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT";   
   DB::statement($sql2);
   }
}