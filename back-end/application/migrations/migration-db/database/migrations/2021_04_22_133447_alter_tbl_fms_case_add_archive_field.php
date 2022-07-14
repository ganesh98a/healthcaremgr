<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFmsCaseAddArchiveField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_fms_case', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_fms_case', 'archive')) {
                $table->unsignedTinyInteger('archive')->default('0')->after('status')->comment('1- Yes, 0- No');
            }
            if (!Schema::hasColumn('tbl_fms_case', 'created_by')) {
                $table->unsignedInteger('created_by')->default('0')->after('created')->comment('Created by');
            }
            if (!Schema::hasColumn('tbl_fms_case', 'updated_by')) {
                $table->unsignedInteger('updated_by')->default('0')->after('updated')->comment('Updated by');
            }
            if (Schema::hasColumn('tbl_fms_case', 'initiated_by')) {
                $table->unsignedInteger('initiated_by')->comment('Id of Member, Participant, ORG, ONCALL User/Admin, Site')->change();
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

    }
}
