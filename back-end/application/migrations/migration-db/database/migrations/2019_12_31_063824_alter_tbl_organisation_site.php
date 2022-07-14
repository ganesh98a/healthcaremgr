<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOrganisationSite extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tbl_organisation_site', function (Blueprint $table) {
			if (!Schema::hasColumn('tbl_organisation_site','payroll_tax')) {
				$table->TinyInteger('payroll_tax')->comment('0-false, 1-true');
			}
			if (!Schema::hasColumn('tbl_organisation_site','gst')) {
				$table->TinyInteger('gst')->comment('0-false, 1-true');
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
		Schema::table('tbl_organisation_site', function (Blueprint $table) {
			if (Schema::hasColumn('tbl_organisation_site','payroll_tax')) {
				$table->dropColumn('payroll_tax');
			}
			if (Schema::hasColumn('tbl_organisation_site','gst')) {
				$table->dropColumn('gst');
			}
		});
	}
}
