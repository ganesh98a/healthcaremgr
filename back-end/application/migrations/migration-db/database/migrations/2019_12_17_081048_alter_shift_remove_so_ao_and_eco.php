<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShiftRemoveSoAoAndEco extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'ao')) {
                $table->dropColumn('ao');
            }
            if (Schema::hasColumn('tbl_shift', 'so')) {
                $table->dropColumn('so');
            }
            if (Schema::hasColumn('tbl_shift', 'eco')) {
                $table->dropColumn('eco');
            }

            if (!Schema::hasColumn('tbl_shift', 'gst')) {
                $table->double('gst', 14, 2)->after('expenses');
            }

            if (!Schema::hasColumn('tbl_shift', 'sub_total')) {
                $table->double('sub_total', 14, 2)->commnet('without gst')->after('gst');
            }
//            if (Schema::hasColumn('tbl_shift', 'price')) {
//                $table->double('price', 14, 2)->commnet('With gst')->change();
//            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'gst')) {
                $table->dropColumn('gst');
            }

            if (Schema::hasColumn('tbl_shift', 'sub_total')) {
                $table->dropColumn('sub_total');
            }
        });
    }

}
