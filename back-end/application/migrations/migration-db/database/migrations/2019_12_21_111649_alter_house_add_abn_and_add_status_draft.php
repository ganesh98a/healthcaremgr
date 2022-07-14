<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterHouseAddAbnAndAddStatusDraft extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_house', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_house', 'abn')) {
                $table->string('abn', 20)->after('logo_file');
            }
            if (!Schema::hasColumn('tbl_house', 'archive')) {
                $table->unsignedSmallInteger('archive')->comment('0- No/1-Yes')->after('updated');
            }
            if (Schema::hasColumn('tbl_house', 'status')) {
                $table->unsignedSmallInteger('status')->comment('1- Active/0-Inactive/2-Draft')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_house', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_house', 'abn')) {
                $table->dropColumn('abn');
            }
        });
    }

}
