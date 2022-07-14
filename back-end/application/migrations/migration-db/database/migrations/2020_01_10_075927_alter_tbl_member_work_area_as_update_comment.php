<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblMemberWorkAreaAsUpdateComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member_work_area', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member_work_area','work_area')) {
                $table->unsignedSmallInteger('work_area')->unsigned()->comment('primary key of tbl_recruitment_applicant_work_area')->change();
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
        Schema::table('tbl_member_work_area', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_member_work_area','work_area')) {
                $table->unsignedSmallInteger('work_area')->unsigned()->comment('1 = Client & NDIS Services, 2 = Out Of Home Care 3 = Disability Accommodation 4 = Casual Staff Service -Disability 5 = Casual Staff Service -Welfare')->change();
            }
        });
    }
}
