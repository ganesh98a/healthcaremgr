<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblPreferredSupportWorkerArea extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_organisation_preferred_support_worker_area')) {
            Schema::create('tbl_organisation_preferred_support_worker_area', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('organisation_id')->nullable()->comment('tbl_organisation.id');
                $table->foreign('organisation_id','org_id_swa_foreign')->references('id')->on('tbl_organisation');
                $table->unsignedInteger('swa_id')->nullable()->comment('tbl_finance_support_worker_area.id');
                $table->foreign('swa_id','pref_swa_id_foreign')->references('id')->on('tbl_finance_support_worker_area');                
                $table->smallInteger('archive')->default(0);
                $table->timestamps();
                $table->unsignedInteger('created_by')->nullable()->comment('tbl_users.id');
                $table->unsignedInteger('updated_by')->nullable()->comment('tbl_users.id'); 
                $table->foreign('created_by','pref_swa_created_by_foreign')->references('id')->on('tbl_users');
                $table->foreign('updated_by','pref_swa_updated_by_foreign')->references('id')->on('tbl_users');
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
        if (Schema::hasTable('tbl_organisation_preferred_support_worker_area')) {
            Schema::dropIfExists('tbl_organisation_preferred_support_worker_area');
        }
    }
}
