<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableShiftAddColumnKeypayCreated extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_shift')) {
            Schema::table('tbl_shift', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_shift', 'keypay_created')) {
                    $table->unsignedTinyInteger("keypay_created")->nullable()->default(0)->after('invoice_created')->comment('1- when shift details send to keypay');
                }
                if (Schema::hasColumn('tbl_shift', 'booked_by')) {
                    $table->unsignedSmallInteger("booked_by")->nullable()->comment('1 - site/2 - participant/3 - location(participant)/4- org/5 - sub-org/6 - reserve in quote/7-house')->change();
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
        if (Schema::hasTable('tbl_shift')) {
            Schema::table('tbl_shift', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_shift', 'keypay_created')) {
                    $table->dropColumn('keypay_created');
                } 
            });
        }
    }
}
