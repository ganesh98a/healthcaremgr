<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblFianaceCostBookMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_finance_cost_book_mapping', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cost_code_id')->comment('tbl_finance_cost_code.id');
            $table->foreign('cost_code_id', 'tbl_fcbm_cost_code_id_foreign')->references('id')->on('tbl_finance_cost_code')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('service_area_id')->comment('tbl_finance_service_area.id');
            $table->foreign('service_area_id')->references('id')->on('tbl_finance_service_area')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('payroll_tax')->nullable()->comment('Payroll Tax 0-Not/1-Yes');
            $table->unsignedSmallInteger('site_discount')->nullable()->comment('Site Discount 0-Not/1-Yes');
            $table->string('cost_book_key_name')->comment('key_name refer the tbl_references.key_name of cost_book type');
            $table->unsignedSmallInteger('archive')->default(0)->comment('0-Not/1-Yes');
            $table->unsignedInteger('created_by')->nullable()->comment('priamry key tbl_users');
            $table->foreign('created_by', 'tbl_fcbm_created_by_foreign')->references('id')->on('tbl_users')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('updated_by')->nullable()->comment('priamry key tbl_users');
            $table->foreign('updated_by', 'tbl_fcbm_updated_by_foreign')->references('id')->on('tbl_users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_finance_cost_book_mapping');
    }
}
