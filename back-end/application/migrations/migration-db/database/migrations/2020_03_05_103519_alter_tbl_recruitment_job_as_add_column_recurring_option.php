<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentJobAsAddColumnRecurringOption extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
     if (Schema::hasTable('tbl_recruitment_job')) {
        Schema::table('tbl_recruitment_job', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_job', 'recurring_type')) {
                $table->unsignedSmallInteger('recurring_type')->comment('1-Weekly, 2-Monthly')->default(0);
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
        if (Schema::hasTable('tbl_recruitment_job')) {
            Schema::table('tbl_recruitment_job', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_job', 'recurring_type')) {
                    $table->dropColumn('recurring_type');
                }
            });
        }
    }
}
