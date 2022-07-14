<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentTaskAddFormId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {	
		if (Schema::hasTable('tbl_recruitment_task')) {
            Schema::table('tbl_recruitment_task', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_recruitment_task', 'form_id')) {
                    $table->unsignedSmallInteger('form_id')->after("status")->comment('tbl_recruitment_form.id');
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
        Schema::table('tbl_recruitment_task', function (Blueprint $table) {
            if (Schema::hasTable('tbl_recruitment_task')) {
            Schema::table('tbl_recruitment_task', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_recruitment_task', 'form_id')) {
                    $table->dropColumn('form_id');
                }
            });
        }
        });
    }
}
