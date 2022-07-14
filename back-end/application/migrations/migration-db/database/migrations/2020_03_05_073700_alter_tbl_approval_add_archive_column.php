<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblApprovalAddArchiveColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_approval')) {
            Schema::table('tbl_approval', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_approval', 'archive')) {
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
       if (Schema::hasTable('tbl_approval')) {
        Schema::table('tbl_approval', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_approval', 'archive')) {
                $table->dropColumn('archive');
            }
        });
    }
}
}
