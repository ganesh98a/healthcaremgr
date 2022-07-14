<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblModuleTitleAddColumnStatusAndItsEnableDisableModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_module_title', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_module_title', 'its_enable_disable_module')) {
                $table->unsignedSmallInteger('its_enable_disable_module')->default(0)->comment("0 - No/1 - Yes");
            }

            if (!Schema::hasColumn('tbl_module_title', 'status')) {
                $table->unsignedSmallInteger('status')->default(0)->comment("0-Disable/1-Enable");
            }

            if (!Schema::hasColumn('tbl_module_title', 'its_allocated_module')) {
                $table->unsignedSmallInteger('its_allocated_module')->default(0)->comment("0 - Not/1 - Yes");
            }
        });

        $seeder = new ModuleTitle();
        $seeder->run();

        $PermissionSeederObj = new PermissionSeeder();
        $PermissionSeederObj->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_module_title', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_module_title', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('tbl_module_title', 'its_enable_disable_module')) {
                $table->dropColumn('its_enable_disable_module');
            }

            if (Schema::hasColumn('tbl_module_title', 'its_allocated_module')) {
                $table->dropColumn('its_allocated_module');
            }
        });
    }
}
