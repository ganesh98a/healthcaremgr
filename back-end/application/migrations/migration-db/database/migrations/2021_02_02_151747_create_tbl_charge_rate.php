<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblChargeRate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_finance_charge_rate')) {
            Schema::create('tbl_finance_charge_rate', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('charge_rate_category_id')->comment('tbl_references.id');
                $table->foreign('charge_rate_category_id')->references('id')->on('tbl_references')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('role_id')->comment('tbl_member_role.id');
                $table->foreign('role_id')->references('id')->on('tbl_member_role')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('pay_level_id')->comment('tbl_references.id');
                $table->foreign('pay_level_id')->references('id')->on('tbl_references')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('skill_level_id')->comment('tbl_references.id');
                $table->foreign('skill_level_id')->references('id')->on('tbl_references')->onUpdate('cascade')->onDelete('cascade');

                $table->unsignedInteger('cost_book_id')->comment('tbl_references.id');
                $table->foreign('cost_book_id')->references('id')->on('tbl_references')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('disability_id');
                $table->unsignedInteger('welfare_id');
                $table->unsignedInteger('day_services_id');
                $table->unsignedInteger('ddso_id');

                $table->dateTime('start_date');
                $table->dateTime('end_date');
                $table->decimal('amount', 10, 2);
                $table->mediumtext('external_reference')->nullable();
                $table->mediumtext('description')->nullable();

                $table->unsignedInteger('status')->default('0')->comment('0 = inactive, 1 = active');
                $table->unsignedInteger('archive')->default('0')->comment('0 = inactive, 1 = active');
                $table->dateTime('created')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
                $table->dateTime('updated')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('tbl_finance_charge_rate', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_charge_rate', 'charge_rate_category_id')) {
                $table->dropForeign(['charge_rate_category_id']);
            }
            if (Schema::hasColumn('tbl_finance_charge_rate', 'pay_level_id')) {
                $table->dropForeign(['pay_level_id']);
            }
            if (Schema::hasColumn('tbl_finance_charge_rate', 'role_id')) {
                $table->dropForeign(['role_id']);
            }
            if (Schema::hasColumn('tbl_finance_charge_rate', 'skill_level_id')) {
                $table->dropForeign(['skill_level_id']);
            }
            if (Schema::hasColumn('tbl_finance_charge_rate', 'cost_book_id')) {
                $table->dropForeign(['cost_book_id']);
            }
            if (Schema::hasColumn('tbl_finance_charge_rate', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('tbl_finance_charge_rate', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });
        Schema::dropIfExists('tbl_finance_charge_rate');
    }
}
