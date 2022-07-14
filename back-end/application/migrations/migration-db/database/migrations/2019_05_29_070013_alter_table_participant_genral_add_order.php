<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableParticipantGenralAddOrder extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('tbl_participant_genral')) {
            Schema::table('tbl_participant_genral', function (Blueprint $table) {
                $table->unsignedInteger('order')->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if (Schema::hasTable('tbl_participant_genral')) {
            Schema::table('tbl_participant_genral', function (Blueprint $table) {
                $table->dropColumn('order');
            });
        }
    }

}
