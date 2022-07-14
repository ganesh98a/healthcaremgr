<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterKeypayAuthDetailsAddKiosksIdColomn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_keypay_auth_details', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_keypay_auth_details', 'kiosks_id')) {
                $table->string('kiosks_id',255)->nullable()->comment('keypay kiosks id')->after('status');
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
        Schema::table('tbl_keypay_auth_details', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_keypay_auth_details', 'kiosks_id')) {
                $table->dropColumn('kiosks_id');
            }
        });
    }
}
