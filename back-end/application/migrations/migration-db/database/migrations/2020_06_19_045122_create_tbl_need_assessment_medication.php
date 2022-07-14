<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblNeedAssessmentMedication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_need_assessment_medication')) {
            Schema::create('tbl_need_assessment_medication', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('need_assessment_id')->comment("tbl_need_assessment.id");
                $table->foreign('need_assessment_id')->references('id')->on('tbl_need_assessment')->onDelete('CASCADE');
                $table->unsignedSmallInteger('not_applicable')->comment("1- Not applicable, 2- applicable");
                $table->unsignedSmallInteger('medication_administration')->comment("0- Not applicable, 1- No, 2- Yes");
                $table->unsignedSmallInteger('medication_emergency')->comment("0- Not applicable, 1- No, 2- Yes");
                $table->unsignedSmallInteger('reduce_concern')->comment("0- Not applicable, 1- No, 2- Yes");

                $table->unsignedSmallInteger('archive')->comment("0- No, 1- Yes");
                $table->timestamp('created')->useCurrent();
                $table->unsignedInteger('created_by');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
                $table->unsignedInteger('updated_by');
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
        Schema::dropIfExists('tbl_need_assessment_medication');
    }
}
