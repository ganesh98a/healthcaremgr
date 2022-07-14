<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFmsCaseAgainstDetailAsUpdateCommentOnAgainstCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_fms_case_against_detail', function (Blueprint $table) {
           if (Schema::hasColumn('tbl_fms_case_against_detail','against_category')) {
                $table->unsignedSmallInteger('against_category')->unsigned()->comment('1- member of public,2- Member, 3- Participant, 4- ONCALL (General), 5- ONCALL User/Admin, 6- Org')->change();
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
        Schema::table('tbl_fms_case_against_detail', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_fms_case_against_detail','against_category')) {
                $table->unsignedSmallInteger('against_category')->unsigned()->comment('1- member of public,2- Member, 3- Participant, 4- ONCALL (General), 5- ONCALL User/Admin')->change();
            }
        });
    }
}
