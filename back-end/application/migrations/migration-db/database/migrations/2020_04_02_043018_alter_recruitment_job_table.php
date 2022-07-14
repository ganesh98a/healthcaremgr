<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRecruitmentJobTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_job', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_job', 'is_cat_publish')) {
                $table->unsignedInteger('is_cat_publish')->after('is_salary_publish')->default("0");
            }
            if (!Schema::hasColumn('tbl_recruitment_job', 'is_subcat_publish')) {
                $table->unsignedInteger('is_subcat_publish')->after('is_cat_publish')->default("0");
            }
            if (!Schema::hasColumn('tbl_recruitment_job', 'is_emptype_publish')) {
                $table->unsignedInteger('is_emptype_publish')->after('is_subcat_publish')->default("0");
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
        Schema::table('tbl_recruitment_job', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_job', 'is_cat_publish')) {
                $table->dropColumn('is_cat_publish');
            }
            if (Schema::hasColumn('tbl_recruitment_job', 'is_subcat_publish')) {
                $table->dropColumn('is_subcat_publish');
            }
            if (Schema::hasColumn('tbl_recruitment_job', 'is_emptype_publish')) {
                $table->dropColumn('is_emptype_publish');
            }
        });
    }
}
