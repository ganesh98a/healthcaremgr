<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddArchiveColumnToRecruitmentForm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_recruitment_form')) {
            Schema::table('tbl_recruitment_form', function(Blueprint $table) {
                $table->unsignedSmallInteger('archive')->default(0)->comment("Soft deletes. yes=1, no=0");
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
        if (Schema::hasTable('tbl_recruitment_form')) {
            Schema::table('tbl_recruitment_form', function(Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_form', 'archive')) {
                    $table->dropColumn('archive');
                }
            });
        }
    }
}
