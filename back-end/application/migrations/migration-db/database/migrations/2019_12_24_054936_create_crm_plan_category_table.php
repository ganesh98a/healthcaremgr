<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmPlanCategoryTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_crm_plan_category', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('key', 255);
            $table->unsignedInteger('order')->comment('Order to show');
            $table->unsignedSmallInteger('cat_type')->default('0')->comment('0-categroy/1-Other');
            $table->datetime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedSmallInteger('archive')->comment('0 - Not/ 1 - Yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_crm_plan_category');
    }

}
