<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCronStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_cron_status')) {
            Schema::create('tbl_cron_status', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamp('last_date_time')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('cron start time');
                $table->string('method_name',255)->nullable()->comment('method for which type of cron set');
                $table->smallInteger('status')->unsigned()->default('1')->comment('1-active and 0- inactive');
                $table->dateTime('created_date')->default('0000-00-00 00:00:00');
                
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
        Schema::dropIfExists('tbl_cron_status');
    }
}
