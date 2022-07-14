<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFmsCaseDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_fms_case_docs')) {
			Schema::create('tbl_fms_case_docs', function(Blueprint $table)
			{
				$table->increments('id');
				$table->unsignedInteger('caseId')->index('caseId');
				$table->string('title', 64);
				$table->string('filename');
				$table->dateTime('created');
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
        Schema::dropIfExists('tbl_fms_case_docs');
    }
}
