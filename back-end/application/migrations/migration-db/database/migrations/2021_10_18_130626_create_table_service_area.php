<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableServiceArea extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_finance_service_area')) {
            Schema::create('tbl_finance_service_area', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->smallInteger('archive')->default(0);
                $table->timestamps();
                $table->unsignedInteger('created_by')->nullable()->comment('tbl_users.id');
                $table->unsignedInteger('updated_by')->nullable()->comment('tbl_users.id'); 
                $table->foreign('created_by')->references('id')->on('tbl_users');
                $table->foreign('updated_by')->references('id')->on('tbl_users'); 
            });
            $seeder = new ServiceArea();
            $seeder->run();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_finance_service_area')) {
            Schema::dropIfExists('tbl_finance_service_area');
        }
    }
}
