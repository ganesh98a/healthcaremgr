<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterModuleTitleAddParantAndKeyColumn extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_module_title', function (Blueprint $table) {
            if (Schema::hasTable('tbl_module_title')) {
                $table->unsignedInteger('parentId')->comment('primay key of self table');
                $table->string('key_name', 255)->comment('Uniqe key and never update')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_module_title', function (Blueprint $table) {
            if (Schema::hasTable('tbl_module_title')) {
                $table->dropColumn('parentId');
                $table->string('key_name', 255)->comment('')->change();
            }
        });
    }

}
