<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblActivityRecipientEntityTypeComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_sales_activity', function (Blueprint $table) {
            $table->unsignedSmallInteger('activity_type')->comment('1-task/2-email/3-call/4-note/5-sms')->change();
            $table->unsignedSmallInteger('entity_type')->unsigned()->comment('1-contact/2-organisation/3-opportunity/4-lead/ 5-serviceagreement/6-shift/7-finance_timesheet/8-application/9-groupbooking')->change();
            $table->unsignedInteger('related_type')->comment('1-opportunity/2-lead/3-service agreement/4-needs assessment/5-Risk assessment/6-shift/7-finance_timesheet/8-application/9-groupbooking')->nullable()->change();
            $table->unsignedInteger('template_id')->comment('reference of (activity_type = (2-tbl_email_templates|5-tbl_sms_templates)).id ')->nullable()->after('comment');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_sales_activity', function (Blueprint $table) {
            $table->unsignedSmallInteger('activity_type')->comment('1-task/2-email/3-call/4-note')->change();
            $table->unsignedSmallInteger('entity_type')->unsigned()->comment('1-contact/2-organisation/3-opportunity')->change();
            $table->unsignedInteger('related_type')->comment('1-opportunity/2-lead/3- service agreement/4-needs assessment/5-Risk assessment')->nullable()->change();
            if (Schema::hasColumn('tbl_sales_activity', 'template_id')) {
                $table->dropColumn('template_id');
            }
        });
    }
}
