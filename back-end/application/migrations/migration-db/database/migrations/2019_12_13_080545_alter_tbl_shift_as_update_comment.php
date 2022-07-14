<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblShiftAsUpdateComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift','created_by')) {
                $table->unsignedSmallInteger('created_by')->unsigned()->comment('0-admin, 1- Participant portal, 2- Org portal')->change();
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
        Schema::table('tbl_shift', function (Blueprint $table) {
             if (Schema::hasColumn('tbl_shift','created_by')) {
                    $table->unsignedSmallInteger('created_by')->unsigned()->comment('1- participant, 0-admin')->change();
                    
                }
        });
    }
}
