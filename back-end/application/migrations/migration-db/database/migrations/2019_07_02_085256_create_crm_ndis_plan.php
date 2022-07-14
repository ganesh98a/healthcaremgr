<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmNdisPlan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_ndis_plan')) {
            Schema::create('tbl_crm_ndis_plan', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('crm_participant_id');
                $table->string('manager_plan',32);
                $table->string('manager_email',64);
                $table->string('manager_address',128);
                $table->unsignedTinyInteger('state');
                $table->unsignedInteger('post_code');
                $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
                $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
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
        Schema::dropIfExists('tbl_crm_ndis_plan');
    }
}
