<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFmsCaseAsAddColumnCompletedDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_fms_case', function (Blueprint $table) {
            if(!Schema::hasColumn('tbl_fms_case','completed_date')){
                $table->dateTime('completed_date')->default('0000-00-00 00:00:00')->comment('date when FMS case is completed/resolve');
            }
            if(!Schema::hasColumn('tbl_fms_case','updated')){
                $table->timestamp('updated')->after('created')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
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
            if(Schema::hasColumn('tbl_fms_case','completed_date')){
                $table->dropColumn('completed_date');
            }
            if(Schema::hasColumn('tbl_fms_case','updated')){
                $table->dropColumn('updated');
            }
        });
    }
}
