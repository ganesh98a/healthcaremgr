<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberSkillAsAddColumnArchive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_member_skill')) {
            Schema::table('tbl_member_skill', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_member_skill', 'archive')) {
                    $table->unsignedSmallInteger('archive')->comment('0 - Not/1 - Yes')->default(0);
                }
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
       if (Schema::hasTable('tbl_member_skill')) {
        Schema::table('tbl_member_skill', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member_skill', 'archive')) {
                $table->dropColumn('archive');
            }
        });
    }
}
}
