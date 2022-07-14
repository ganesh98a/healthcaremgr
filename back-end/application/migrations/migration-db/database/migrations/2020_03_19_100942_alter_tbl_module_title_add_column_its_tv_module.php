<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblModuleTitleAddColumnItsTvModule extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        if (Schema::hasTable('tbl_module_title')) {
            Schema::table('tbl_module_title', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_module_title', 'its_tv_module')) {
                    $table->unsignedSmallInteger('its_tv_module')->default(0)->comment("0 - No/1 - Yes");
                }
            });
            
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_module_title', function (Blueprint $table) {
            if (Schema::hasTable('tbl_module_title')) {
                Schema::table('tbl_module_title', function (Blueprint $table) {
                    if (Schema::hasColumn('tbl_module_title', 'its_tv_module')) {
                        $table->dropColumn('its_tv_module');
                    }
                });
            }
        });
    }

}
