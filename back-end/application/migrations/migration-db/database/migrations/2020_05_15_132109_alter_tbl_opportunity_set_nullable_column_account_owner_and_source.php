<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOpportunitySetNullableColumnAccountOwnerAndSource extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_opportunity', function (Blueprint $table) {
			if (Schema::hasColumn('tbl_opportunity', 'opportunity_type')) {
				$table->integer('opportunity_type')->unsigned()->comment('tbl_opportunity_type.id')->nullable()->change();
			}
			
			if (Schema::hasColumn('tbl_opportunity', 'account_person')) {
				 $table->bigInteger('account_person')->unsigned()->comment("tbl_person.id")->nullable()->change();
			}
			
			if (Schema::hasColumn('tbl_opportunity', 'created_by')) {
				$table->unsignedInteger('created_by')->comment("tbl_member.id created by")->nullable()->change();
			}
			
			if (Schema::hasColumn('tbl_opportunity', 'opportunity_type')) {
				$table->unsignedInteger('opportunity_type')->comment("tbl_member.id created by")->nullable()->change();
			}
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_opportunity', function (Blueprint $table) {
            //
        });
    }
}
