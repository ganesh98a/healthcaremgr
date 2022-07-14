<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPermissionAddModuleId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_permission', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_permission', 'moduleId')) {
                $table->unsignedInteger('moduleId')->comment('primary key tbl_module_title');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_permission', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_permission', 'moduleId')) {
                $table->dropColumn('moduleId');
            }
        });
    }

}
