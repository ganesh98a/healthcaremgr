<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableApplicationHistoryFeedAddFeedType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_application_history_feed', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_application_history_feed', 'feed_type')) {
                $table->smallInteger('feed_type')->nullable()->default(null)->comment('1 => sms, null => other');
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
        Schema::table('tbl_application_history_feed', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_application_history_feed', 'feed_type')) {
                $table->dropColumn('feed_type');
            }
        });
    }
}
