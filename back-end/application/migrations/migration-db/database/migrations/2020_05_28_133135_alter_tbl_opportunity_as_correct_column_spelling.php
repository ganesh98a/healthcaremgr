<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOpportunityAsCorrectColumnSpelling extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_opportunity', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_opportunity', 'neeed_support_plan')) {
                $table->renameColumn('neeed_support_plan', 'need_support_plan');
            }

            if (!Schema::hasColumn('tbl_opportunity', 'updated_by')) {
                $table->unsignedInteger('updated_by')->comment('tbl_member.id')->after("created_by");
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
        Schema::table('tbl_opportunity', function (Blueprint $table) {
            //
        });
    }
}
