<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterParticipantGenralAddKeyName extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_participant_genral', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_participant_genral', 'key_name')) {
                $table->string('key_name', 200)->after("name");
            }
        });

        $obj = new ParticipantGenralSeeder();
        $obj->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_participant_genral', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_participant_genral', 'key_name')) {
                $table->dropColumn('key_name');
            }
        });
    }

}
