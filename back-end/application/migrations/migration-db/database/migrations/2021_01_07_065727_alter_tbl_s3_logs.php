<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblS3Logs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_s3_logs', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_s3_logs', 'module_id')) {

                $table->unsignedInteger('module_id')->comment('1-Recruitment/2-Member/3-Sales')->change();

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
        Schema::dropIfExists('tbl_s3_logs');
    }
}
