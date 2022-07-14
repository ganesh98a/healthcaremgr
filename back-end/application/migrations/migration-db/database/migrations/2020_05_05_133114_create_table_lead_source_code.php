<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableLeadSourceCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_lead_source_code')) {
            Schema::create('tbl_lead_source_code', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name',255)->comment('label show on ui end');
                $table->string('key_name',255)->comment('uniuqe key_name');
                $table->unsignedMediumInteger('order_ref')->comment('order_ref');
                $table->unsignedSmallInteger('archive')->default(0)->comment('no=0, yes=1')->index();
            });

            if(Schema::hasTable('tbl_lead_source_code')){
                $seeder = new LeadSourceCode();
                $seeder->run();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_lead_source_code');
    }
}
