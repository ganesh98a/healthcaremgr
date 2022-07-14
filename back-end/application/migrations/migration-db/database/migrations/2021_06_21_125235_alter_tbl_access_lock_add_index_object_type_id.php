<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblAccessLockAddIndexObjectTypeId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_access_lock')) {       
            DB::statement("DELETE FROM tbl_access_lock WHERE archive = 1");
        }
        
        Schema::table('tbl_access_lock', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_access_lock', 'object_type_id')) {
                $table->unsignedInteger('object_type_id')->default('1')->comment('1 = shift')->index('object_type_id')->change();
            }

            if (Schema::hasColumn('tbl_access_lock', 'object_id')) {
                $table->unsignedInteger('object_id')->index('object_id')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_access_lock', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_access_lock', 'object_type_id')) {
                $table->unsignedInteger('object_type_id')->default('1')->comment('1 = shift')->change();
            }

            if (Schema::hasColumn('tbl_access_lock', 'object_id')) {
                $table->unsignedInteger('object_id')->change();
            }
        });
    }
}
