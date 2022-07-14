<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableDepartment extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (Schema::hasTable('tbl_department')) {
            Schema::table('tbl_department', function (Blueprint $table) {
                $table->string('short_code', 30)->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if (Schema::hasTable('tbl_department')) {
            Schema::table('tbl_department', function (Blueprint $table) {
                $table->dropColumn('short_code');
            });
        }
    }

}
