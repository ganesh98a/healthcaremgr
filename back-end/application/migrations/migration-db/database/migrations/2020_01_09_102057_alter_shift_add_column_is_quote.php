<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShiftAddColumnIsQuote extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift', 'is_quoted')) {
                $table->unsignedInteger('is_quoted')->comment('0 - No/1 - Yes')->after('push_to_app');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'is_quoted')) {
                $table->dropColumn('is_quoted');
            }
        });
    }

}
