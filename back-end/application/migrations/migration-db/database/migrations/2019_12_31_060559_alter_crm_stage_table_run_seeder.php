<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCrmStageTableRunSeeder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('tbl_crm_stage', function (Blueprint $table) {
            $seeder = new CrmStageSeeder();
            $seeder->run();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_crm_stage', function (Blueprint $table) {
            //
        });
    }
}
