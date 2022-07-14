<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOwnerColumnTblRecruitmentJob extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_job', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_job', 'owner')) {
                $table->unsignedInteger('owner')->nullable()->comment('reference of tbl_member.id');
                 $table->foreign('owner')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
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
            if (Schema::hasColumn('tbl_recruitment_job', 'owner')) {
                $table->dropForeign(['owner']);
                $table->dropColumn('owner');
            }
        });
    }
}

