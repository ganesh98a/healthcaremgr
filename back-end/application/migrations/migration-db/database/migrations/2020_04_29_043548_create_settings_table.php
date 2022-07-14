<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasTable('tbl_settings')) {
            Schema::create('tbl_settings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('key', 255)->comment('Must be unique. No spaces. You may use any convention you want. (Eg. DOCUSIGN_API_KEY, or DOCUSIGN.SECRET, user.12314.idx, _autostart)');
                // $table->unique('key');

                $table->mediumText('value')->comment('bool=0/1 numbers=123131 string=anything json={"str_val":0,"bool":false,"obj":{}, "array":[]}');

                $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('tbl_settings');
    }
}
