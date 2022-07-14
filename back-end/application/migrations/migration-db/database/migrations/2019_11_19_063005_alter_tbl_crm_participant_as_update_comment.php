<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblCrmParticipantAsUpdateComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_crm_participant', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_crm_participant','intake_type')) {
                $table->unsignedSmallInteger('intake_type')->unsigned()->comment('1- new, 2- rejected,3- renewed,4- Returning, 5-Modified')->change();
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
        Schema::table('tbl_crm_participant', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_crm_participant','intake_type')) {
                $table->unsignedSmallInteger('intake_type')->unsigned()->comment('1- new, 2- rejected,3- renewed')->change();
            }
        });
    }
}
