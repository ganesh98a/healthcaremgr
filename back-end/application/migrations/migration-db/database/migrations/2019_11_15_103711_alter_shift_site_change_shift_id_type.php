<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShiftSiteChangeShiftIdType extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_shift_site', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_site', 'shiftId')) {
                $table->unsignedInteger('shiftId')->default(0)->comment('tbl_shift auto increment id')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_shift_site', function (Blueprint $table) {
            //
        });
    }

}
