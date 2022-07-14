<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblNotificationEntityType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_notification', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_notification', 'entity_type')) {

                $table->unsignedInteger('entity_type')->comment('1-opportunity/2-lead/3- service agreement/4-needs assessment/5-Risk assessment/6-ServiceAgreement Contract/7-Quiz submitted/8-Shift Published/9-Email Notification')->change();

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

    }
}
