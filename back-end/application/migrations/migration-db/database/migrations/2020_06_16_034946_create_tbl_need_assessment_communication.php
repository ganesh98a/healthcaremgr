<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblNeedAssessmentCommunication extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_need_assessment_communication')) {
            Schema::create('tbl_need_assessment_communication', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('need_assessment_id')->comment("tbl_need_assessment.id");
                $table->foreign('need_assessment_id')->references('id')->on('tbl_need_assessment')->onDelete('CASCADE');
                $table->unsignedSmallInteger('communication_verbal');
                $table->unsignedSmallInteger('communication_book');
                $table->unsignedSmallInteger('communication_nonverbal');
                $table->unsignedSmallInteger('communication_electric');
                $table->unsignedSmallInteger('communication_vocalization');
                $table->unsignedSmallInteger('communication_sign');
                $table->unsignedSmallInteger('communication_other');
                $table->string('communication_other_desc',1000);

                $table->unsignedSmallInteger('interpreter')->comment("2- Yes, 1- No");
                $table->unsignedSmallInteger('cognition')->comment("2- Yes, 1- No");
                $table->unsignedSmallInteger('instructions')->comment("2- Yes, 1- No");
                $table->string('instructions_desc',1000);
                $table->unsignedSmallInteger('hearing_impared')->comment("2- Yes, 1- No");
                $table->string('hearing_impared_desc',1000);
                $table->unsignedSmallInteger('visually_impared')->comment("2- Yes, 1- No");
                $table->string('visually_impared_desc',1000);

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
        Schema::dropIfExists('tbl_need_assessment_communication');
    }
}
