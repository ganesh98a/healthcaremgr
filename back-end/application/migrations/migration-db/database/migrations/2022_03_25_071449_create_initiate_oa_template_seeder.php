<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInitiateOaTemplateSeeder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_sms_template', function (Blueprint $table) {            
            if (!Schema::hasColumn('tbl_sms_template', 'used_to_initiate_oa')) {
                $table->smallInteger('used_to_initiate_oa')->nullable()->default(0)->comment('1=>Template is used in automated sms like OA initiated');
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
        Schema::table('tbl_sms_template', function (Blueprint $table) {            
            if (Schema::hasColumn('tbl_sms_template', 'used_to_initiate_oa')) {
                $table->dropColumn('used_to_initiate_oa');
            }
        });
    }
}
