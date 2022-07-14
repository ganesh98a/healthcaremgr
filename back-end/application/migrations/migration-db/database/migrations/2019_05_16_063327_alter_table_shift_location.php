<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableShiftLocation extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('tbl_shift_location')) {
            Schema::table('tbl_shift_location', function (Blueprint $table) {
                $table->string('lat', 50)->nullable()->change();
                $table->string('long', 50)->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if (Schema::hasTable('tbl_shift_location')) {
            Schema::table('tbl_shift_location', function (Blueprint $table) {
                $table->string('lat', 50)->change();
                $table->string('long', 50)->change();
            });
        }
    }

}
