<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblSalesRelation extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if (!Schema::hasTable('tbl_sales_relation')) {
            Schema::create('tbl_sales_relation', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('source_data_id');
                $table->unsignedInteger('source_data_type')->comment("1 - Contact/ 2 - Organisation/ 3 - opportunity");
                $table->unsignedInteger('destination_data_id');
                $table->unsignedInteger('destination_data_type')->comment("1 - Contact/ 2 - Organisation/ 3 - opportunity");

                $table->unsignedInteger('roll_id');
                $table->unsignedTinyInteger('is_primary')->comment("0-No/1-Yes");
                $table->timestamp('created')->useCurrent();
                $table->integer('created_by')->unsigned()->nullable();
                $table->foreign('created_by')->references('id')->on('tbl_member')->onDelete('CASCADE');

                $table->dateTime('updated');
                $table->integer('updated_by')->unsigned()->nullable();
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onDelete('CASCADE');
                
                $table->unsignedSmallInteger('archive')->comment("0-No/1-Yes");
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_sales_relation');
    }

}
