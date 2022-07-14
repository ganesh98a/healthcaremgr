<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableMemberWorkAreaUpdated extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_member_work_area')) {
            Schema::table('tbl_member_work_area', function (Blueprint $table) {
                $table->timestamp('updated')->useCurrent();
            });
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_member_work_area', function (Blueprint $table) {
            //
        });
    }
}
