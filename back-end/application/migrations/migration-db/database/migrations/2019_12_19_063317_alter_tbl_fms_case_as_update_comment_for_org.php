<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFmsCaseAsUpdateCommentForOrg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_fms_case', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_fms_case','initiated_type')) {
                $table->unsignedSmallInteger('initiated_type')->unsigned()->comment('1- Member, 2- Participant, 3- ORG, 4- House, 5- member of public, 6- ONCALL (General), 7- ONCALL User/Admin, 8-Org')->change();
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
        Schema::table('tbl_fms_case', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_fms_case','initiated_type')) {
                $table->unsignedSmallInteger('initiated_type')->unsigned()->comment('1- Member, 2- Participant, 3- ORG, 4- House, 5- member of public, 6- ONCALL (General), 7- ONCALL User/Admin')->change();
            }
        });
    }
}
