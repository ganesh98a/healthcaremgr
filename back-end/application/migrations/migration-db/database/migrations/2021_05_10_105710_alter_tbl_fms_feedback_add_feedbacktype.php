<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFmsFeedbackAddFeedbacktype extends Migration
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
                if (!Schema::hasColumn('tbl_fms_feedback', 'feedback_type')) {
                    $table->unsignedInteger('feedback_type')->nullable()->after("alert_type")
                    ->comments('1- Complaint, 2- Reportable Incident, 3- Other Feedback');
                }
            });
            DB::statement("DELETE from tbl_references where key_name IN ('init_hcm_user_admin', 'aga_hcm_user_admin')");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
