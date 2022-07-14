<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblNeedAssessmentPersonalcare extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_need_assessment_personalcare')) {
            Schema::create('tbl_need_assessment_personalcare', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('need_assessment_id')->comment("tbl_need_assessment.id");
                $table->foreign('need_assessment_id')->references('id')->on('tbl_need_assessment')->onDelete('CASCADE');
                $table->unsignedSmallInteger('not_applicable')->comment("1- Not applicable, 2- applicable");
                $table->unsignedSmallInteger('bowelcare')->comment("0- Not applicable, 1- with assistance, 2- with supervision, 3- independant");
                $table->unsignedSmallInteger('bladdercare')->comment("0- Not applicable, 1- with assistance, 2- with supervision, 3- independant");
                $table->unsignedSmallInteger('showercare')->comment("0- Not applicable, 1- with assistance, 2- with supervision, 3- independant");
                $table->unsignedSmallInteger('dressing')->comment("0- Not applicable, 1- with assistance, 2- with supervision, 3- independant");
                $table->unsignedSmallInteger('teethcleaning')->comment("0- Not applicable, 1- with assistance, 2- with supervision, 3- independant");
                $table->unsignedSmallInteger('cooking')->comment("0- Not applicable, 1- with assistance, 2- with supervision, 3- independant");
                $table->unsignedSmallInteger('eating')->comment("0- Not applicable, 1- with assistance, 2- with supervision, 3- independant");
                $table->unsignedSmallInteger('drinking')->comment("0- Not applicable, 1- with assistance, 2- with supervision, 3- independant");
                $table->unsignedSmallInteger('lighthousework')->comment("0- Not applicable, 1- with assistance, 2- with supervision, 3- independant");
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
        Schema::dropIfExists('tbl_need_assessment_personalcare');
    }
}
