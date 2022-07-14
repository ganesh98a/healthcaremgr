<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableShiftAddColumnKeypayShiftId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('tbl_shift')){
            Schema::table('tbl_shift', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_shift','keypay_shift_id')){
                    $table->string('keypay_shift_id',255)->nullable()->comment('keypay api unique shift id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasTable('tbl_shift')){
            Schema::table('tbl_shift', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_shift','keypay_shift_id')){
                    $table->dropColumn('keypay_shift_id');
                }
            });
        }
    }
}
