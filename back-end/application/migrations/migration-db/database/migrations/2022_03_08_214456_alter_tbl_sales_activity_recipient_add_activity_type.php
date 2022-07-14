<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblSalesActivityRecipientAddActivityType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_sales_activity_recipient', function (Blueprint $table) {
            $table->unsignedSmallInteger('activity_type')->comment('1-task/2-email/3-call/4-note/5-sms')->after('id');
            $table->unsignedInteger('entity_type')->nullable()->comment('1-contact/2-organisation/3-opportunity/4-lead/ 5-serviceagreement/6-shift/7-finance_timesheet/8-application/9-groupbooking')->change();
        });
        DB::statement("UPDATE tbl_sales_activity_recipient SET activity_type = 2 ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_sales_activity_recipient', function (Blueprint $table) {
            $table->unsignedInteger('entity_type')->nullable()->comment('1 - contact / 4 - lead')->change();
            if (Schema::hasColumn('tbl_sales_activity_recipient', 'activity_type')) {
                $table->dropColumn('activity_type');
            }
        });
    }
}
