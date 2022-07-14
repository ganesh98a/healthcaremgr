<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSubModuleTitleRenameToModuleTitle extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_sub_module_title', function (Blueprint $table) {
            if (Schema::hasTable('tbl_sub_module_title')) {
                Schema::rename('tbl_sub_module_title', 'tbl_module_title');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_sub_module_title', function (Blueprint $table) {
            if (Schema::hasTable('tbl_module_title')) {
                Schema::rename('tbl_module_title', 'tbl_sub_module_title');
            }
        });
    }

}
