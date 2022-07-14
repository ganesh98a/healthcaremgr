<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableStaffSafteyChecklistCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_staff_saftey_checklist_categories')) {
            Schema::create('tbl_staff_saftey_checklist_categories', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('category_name');
                $table->unsignedInteger('archive')->default(0)->nullable()->comment('1 - Yes / 0 - No');
                $table->timestamps();  
            });
        }

        if (!Schema::hasTable('tbl_staff_saftey_checklist_items')) {
            Schema::create('tbl_staff_saftey_checklist_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('category_id');
                $table->foreign('category_id')->references('id')->on('tbl_staff_saftey_checklist_categories')->onUpdate('cascade')->onDelete('cascade');
                $table->string('item_name');
                $table->unsignedInteger('archive')->default(0)->nullable()->comment('1 - Yes / 0 - No');
                $table->timestamps();  
            });
        }
        if (!Schema::hasTable('tbl_opportunity_staff_saftey_checklist')) {
            Schema::create('tbl_opportunity_staff_saftey_checklist', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('opportunity_id');
                $table->foreign('opportunity_id')->references('id')->on('tbl_opportunity')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedBigInteger('item_id');
                $table->foreign('item_id')->references('id')->on('tbl_staff_saftey_checklist_items')->onUpdate('cascade')->onDelete('cascade');
                $table->tinyInteger('item_value')->nullable()->default(null);
                $table->text('item_details');
                $table->unsignedInteger('created_by')->comment('the user who initiated the field change, or zero if initiated by the system');
                $table->foreign('created_by')->references('id')->on('tbl_member');          // do not cascade
                $table->unsignedInteger('updated_by')->comment('the user who initiated the field change, or zero if initiated by the system');
                $table->foreign('updated_by')->references('id')->on('tbl_member');          // do not cascade
                $table->unsignedInteger('archive')->default(0)->nullable()->comment('1 - Yes / 0 - No');
                $table->timestamps();  
            });
        }
        $seeder = new SafetyChecklistCategories();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_opportunity_staff_saftey_checklist');
        Schema::dropIfExists('tbl_staff_saftey_checklist_items');
        Schema::dropIfExists('tbl_staff_saftey_checklist_categories');        
    }
}
