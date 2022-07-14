<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFmsCaseNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_fms_case_notes')) {
            Schema::create('tbl_fms_case_notes', function(Blueprint $table)
                {
                    $table->increments('id');
                    $table->unsignedInteger('caseId')->index('caseId');
                    $table->string('title', 228)->nullable();
                    $table->text('description');
                    $table->unsignedInteger('created_by')->index('created_by');
                    $table->unsignedInteger('created_type')->comment('1- Member, 2- Participant, 3- ORG, 4- House, 5- member of public');
                    $table->dateTime('created');
                    $table->dateTime('updated')->default('0000-00-00 00:00:00');
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
        Schema::dropIfExists('tbl_fms_case_notes');
    }
}
