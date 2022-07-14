<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFmsCaseNotesAddColumnShiftDateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_fms_case_notes', function (Blueprint $table) {
            if(!Schema::hasColumn('tbl_fms_case_notes','shift_date')){
                $table->dateTime('shift_date')->default('0000-00-00 00:00:00')->after('created_type');
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
        Schema::table('tbl_fms_case_notes', function (Blueprint $table) {
            if(Schema::hasColumn('tbl_fms_case_notes','shift_date')){
                $table->dropColumn('shift_date');
            }            
        });
    }
}
