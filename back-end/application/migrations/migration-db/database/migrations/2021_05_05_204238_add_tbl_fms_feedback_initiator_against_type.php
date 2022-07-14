<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTblFmsFeedbackInitiatorAgainstType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_fms_feedback')) {
            Schema::table('tbl_fms_feedback', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_fms_feedback', 'initiator_type')) {
                    $table->unsignedInteger('initiator_type')->nullable()->after("description")
                    ->comments('1- Member, 2- Participant, 3- ONCALL User/Admin, 4- ORG, 5-Site,  6- member of public, 7- ONCALL (General)');
                }

                if (!Schema::hasColumn('tbl_fms_feedback', 'against_type')) {
                    $table->unsignedInteger('against_type')->nullable()->after("initiator_type")
                    ->comments('1- HCM Member, 2- HCM Participant, 3- HCM User/Admin,
                    4- HCM Organisation, 5-HCM Site,  6- Member of Public, 7- HCM (General)');
                }

                if (!Schema::hasColumn('tbl_fms_feedback', 'alert_type')) {
                    $table->unsignedInteger('alert_type')->nullable()->after("status")
                    ->comments('1-Member Alert, 2- Organisation/Participant Alert');
                }
                if (!Schema::hasColumn('tbl_fms_feedback', 'feedback_type')) {
                    $table->unsignedInteger('feedback_type')->nullable()->after("alert_type")
                    ->comments('1-Compliment, 2-Comment, 3-Complaint');
                }
            });

            # Update the Existing Feedback id if exists
            $res = DB::select("SELECT tbl_fms_feedback.id, (SELECT `int_ref`.`display_name` as `int_display_name` FROM `tbl_references` as `int_ref` WHERE int_ref.id = tbl_fms_feedback.initiated_type LIMIT 1) as init_category, (SELECT `against_ref`.`display_name` as `against_display_name` FROM `tbl_references` as `against_ref` WHERE against_ref.id = against.against_category LIMIT 1) as against_category FROM `tbl_fms_feedback` INNER JOIN `tbl_fms_feedback_category` ON `tbl_fms_feedback_category`.`caseId` = `tbl_fms_feedback`.`id` INNER JOIN `tbl_fms_feedback_against_detail` as `against` ON `against`.`caseId` = `tbl_fms_feedback`.`id` GROUP BY `tbl_fms_feedback`.`id`");

            if(!empty($res)) {
                $type = [ 'HCM Member' => 1, 'HCM Participant' => 2, 'HCM User/Admin' => 3, 'HCM Organisation' => 4, 'HCM Site' => 5, 'Member of Public' => 6, 'HCM (General)' => 7];

                foreach($res as $row) {

                    $initiator_type = !empty($type[trim($row->init_category)]) ? $type[trim($row->init_category)] : NULL;

                    $against_type = !empty($type[trim($row->against_category)]) ? $type[trim($row->against_category)]:  NULL;
                    DB::statement("UPDATE `tbl_fms_feedback` SET `initiator_type` = '{$initiator_type}'
                        , `against_type` = '{$against_type}' where id = {$row->id}");
                }
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
        Schema::table('tbl_fms_feedback', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_fms_feedback', 'initiator_type')) {
                $table->dropColumn('initiator_type');
            }
            if (Schema::hasColumn('tbl_fms_feedback', 'alert_type')) {
                $table->dropColumn('alert_type');
            }
            if (Schema::hasColumn('tbl_fms_feedback', 'feedback_type')) {
                $table->dropColumn('feedback_type');
            }
          });
    }
}
