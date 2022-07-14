<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblPayRate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_finance_pay_rate')) {
            Schema::create('tbl_finance_pay_rate', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('pay_rate_category_id')->comment('tbl_references.id');
                $table->foreign('pay_rate_category_id')->references('id')->on('tbl_references')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('pay_rate_award_id')->comment('tbl_references.id');
                $table->foreign('pay_rate_award_id')->references('id')->on('tbl_references')->onUpdate('cascade')->onDelete('cascade');

                $table->unsignedInteger('role_id')->comment('tbl_member_role.id');
                $table->foreign('role_id')->references('id')->on('tbl_member_role')->onUpdate('cascade')->onDelete('cascade');

                $table->unsignedInteger('pay_level_id')->comment('tbl_references.id');
                $table->foreign('pay_level_id')->references('id')->on('tbl_references')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('skill_level_id')->comment('tbl_references.id');
                $table->foreign('skill_level_id')->references('id')->on('tbl_references')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('employment_type_id')->comment('tbl_references.id');
                $table->foreign('employment_type_id')->references('id')->on('tbl_references')->onUpdate('cascade')->onDelete('cascade');

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
        Schema::table('tbl_finance_pay_rate', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_finance_pay_rate', 'pay_rate_category_id')) {
                $table->dropForeign(['pay_rate_category_id']);
            }
            if (Schema::hasColumn('tbl_finance_pay_rate', 'pay_rate_award_id')) {
                $table->dropForeign(['pay_rate_award_id']);
            }
            if (Schema::hasColumn('tbl_finance_pay_rate', 'pay_level_id')) {
                $table->dropForeign(['pay_level_id']);
            }

            if (Schema::hasColumn('tbl_finance_pay_rate', 'role_id')) {
                $table->dropForeign(['role_id']);
            }
            if (Schema::hasColumn('tbl_finance_pay_rate', 'skill_level_id')) {
                $table->dropForeign(['skill_level_id']);
            }
            if (Schema::hasColumn('tbl_finance_pay_rate', 'employment_type_id')) {
                $table->dropForeign(['employment_type_id']);
            }
            
            if (Schema::hasColumn('tbl_finance_pay_rate', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('tbl_finance_pay_rate', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });
        Schema::dropIfExists('tbl_finance_pay_rate');
    }
}
