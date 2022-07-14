<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFinanceOrganisationServiceArea extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_organisation_service_area')) {
            Schema::create('tbl_organisation_service_area', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('organisation_id')->nullable()->comment('tbl_organisation.id');
                $table->foreign('organisation_id')->references('id')->on('tbl_organisation');
                $table->unsignedInteger('service_area_id')->nullable()->comment('tbl_finance_service_area.id');
                $table->foreign('service_area_id')->references('id')->on('tbl_finance_service_area');                
                $table->smallInteger('archive')->default(0);
                $table->timestamps();
                $table->unsignedInteger('created_by')->nullable()->comment('tbl_users.id');
                $table->unsignedInteger('updated_by')->nullable()->comment('tbl_users.id'); 
                $table->foreign('created_by')->references('id')->on('tbl_users');
                $table->foreign('updated_by')->references('id')->on('tbl_users');
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
        if (Schema::hasTable('tbl_organisation_service_area')) {
            Schema::dropIfExists('tbl_organisation_service_area');
        }
    }
}
