<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblGoalsMaster extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_goals_master', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_goals_master', 'archive')) {
                $table->unsignedInteger('archive')->default('0')->comment('0 = inactive, 1 = active')->after("end_date");
                $table->dateTime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->dateTime('updated')->nullable();
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
        Schema::table('tbl_goals_master', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_goals_master', 'archive')) {
                $table->dropColumn('archive');
            }
            if (Schema::hasColumn('tbl_goals_master', 'created')) {
                $table->dropColumn('created');
            }
            if (Schema::hasColumn('tbl_goals_master', 'updated')) {
                $table->dropColumn('updated');
            }
        });
    }
}
