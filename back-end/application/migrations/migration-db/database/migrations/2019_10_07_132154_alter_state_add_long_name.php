<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterStateAddLongName extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_state', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_state', 'long_name')) {
                $table->string('long_name', 200)->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_state', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_state', 'long_name')) {
                $table->dropColumn('long_name');
            }
        });
    }

}
