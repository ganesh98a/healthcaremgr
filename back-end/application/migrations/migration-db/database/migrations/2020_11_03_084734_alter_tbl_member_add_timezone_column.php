<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberAddTimezoneColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_member', 'timezone')) {
                $table->text('timezone')->nullable()->after('department');
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
        Schema::table('tbl_person', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member', 'timezone')) {
                $table->dropColumn('timezone');
            }
        });
    }
}
