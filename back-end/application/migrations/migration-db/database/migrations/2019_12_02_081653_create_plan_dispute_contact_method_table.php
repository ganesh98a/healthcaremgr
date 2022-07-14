<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanDisputeContactMethodTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        if (!Schema::hasTable('tbl_plan_dispute_contact_method')) {
            Schema::create('tbl_plan_dispute_contact_method', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 200);
                $table->unsignedSmallInteger('archive')->comment('0 for No/1 for Yes');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_plan_dispute_contact_method');
    }

}
