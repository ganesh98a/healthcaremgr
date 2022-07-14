<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrateTblFinanceSupportPurposeMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_finance_support_purpose_mapping', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('support_category_id')->nullable()->comment('primay key of tbl_finance_support_category');
            $table->unsignedTinyInteger('support_purpose_id')->nullable()->comment('primay key of tbl_finance_support_purpose');
            $table->unsignedTinyInteger('archive')->default(0)->comment('0- not archive, 1- archive data');
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
        Schema::dropIfExists('tbl_finance_support_purpose_mapping');
    }
}
