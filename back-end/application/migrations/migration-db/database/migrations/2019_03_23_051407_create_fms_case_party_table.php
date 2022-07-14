<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFmsCasePartyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_fms_case_party')) {
            Schema::create('tbl_fms_case_party', function(Blueprint $table)
                {
                    $table->unsignedInteger('caseId')->index('caseId');
                    $table->unsignedInteger('againstId')->index('againstId');
                    $table->unsignedTinyInteger('against_type')->comment('1- Member, 2- Participant, 3- ORG, 4- House');
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
        Schema::dropIfExists('tbl_fms_case_party');
    }
}
