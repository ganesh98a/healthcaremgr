<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableServiceAreaSupportWorkerAreaMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_finance_service_area_swa_mapping')) {
            Schema::create('tbl_finance_service_area_swa_mapping', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('service_area_id')->nullable()->comment('tbl_finance_service_area.id');
                $table->foreign('service_area_id')->references('id')->on('tbl_finance_service_area');
                $table->unsignedInteger('swa_id')->nullable()->comment('tbl_finance_support_worker_area.id');
                $table->foreign('swa_id')->references('id')->on('tbl_finance_support_worker_area');
                $table->unsignedInteger('cost_code_id')->nullable()->comment('tbl_finance_cost_code.id');
                $table->foreign('cost_code_id')->references('id')->on('tbl_finance_cost_code');
                $table->smallInteger('archive')->default(0);
                $table->timestamps();
                $table->unsignedInteger('created_by')->nullable()->comment('tbl_users.id');
                $table->unsignedInteger('updated_by')->nullable()->comment('tbl_users.id'); 
                $table->foreign('created_by')->references('id')->on('tbl_users');
                $table->foreign('updated_by')->references('id')->on('tbl_users');
            });
            $seeder = new ServiceAreaSWA();
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
        if (Schema::hasTable('tbl_finance_service_area_swa_mapping')) {
            Schema::dropIfExists('tbl_finance_service_area_swa_mapping');
        }
    }
}
