<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RunInitiateOaTemplateSeeder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_sms_template', function (Blueprint $table) { 
            if (Schema::hasColumn('tbl_sms_template', 'used_to_initiate_oa')) {
                $seeder = new InitiateOASMSTemplateSeeder();
                $seeder->run();
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
        $seeder = new InitiateOASMSTemplateSeeder();
        $seeder->undoRun();
    }
}
