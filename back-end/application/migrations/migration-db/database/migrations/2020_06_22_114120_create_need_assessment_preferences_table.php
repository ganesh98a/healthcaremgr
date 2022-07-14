<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNeedAssessmentPreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_need_assessment_preferences', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('need_assessment_id')->comment("tbl_need_assessment.id");
            $table->foreign('need_assessment_id')->references('id')->on('tbl_need_assessment')->onDelete('CASCADE');
            $table->date('prefered_start_date');
            $table->unsignedSmallInteger('support_worker_gender')->comment("Female- 1, Male- 2, Either-3");

            $table->unsignedSmallInteger('known_unknown_worker')->comment("checked- 1, un-checked- 0 (come under `Vacant Shifts`)");
            $table->unsignedSmallInteger('meet_greet_required')->comment("checked- 1, un-checked- 0 (come under `Vacant Shifts`)");
            $table->unsignedSmallInteger('shadow_shift')->comment("checked- 1, un-checked- 0 (come under `Vacant Shifts`)");
            $table->unsignedSmallInteger('contact_shift')->comment("checked- 1, un-checked- 0 (come under `Vacant Shifts`)");

            $table->unsignedSmallInteger('hs_weekday')->comment("checked- 1, un-checked- 0 (come under `In home Support`)");
            $table->unsignedSmallInteger('hs_saturday')->comment("checked- 1, un-checked- 0 (come under `In home Support`)");
            $table->unsignedSmallInteger('hs_sunday')->comment("checked- 1, un-checked- 0 (come under `In home Support`)");
            $table->unsignedSmallInteger('hs_sleep_over')->comment("checked- 1, un-checked- 0 (come under `In home Support`)");
            $table->unsignedSmallInteger('hs_active_night')->comment("checked- 1, un-checked- 0 (come under `In home Support`)");
            $table->unsignedSmallInteger('hs_public_holiday')->comment("checked- 1, un-checked- 0 (come under `In home Support`)");

            $table->unsignedSmallInteger('as_weekday')->comment("checked- 1, un-checked- 0 (come under `Community Access Support`)");
            $table->unsignedSmallInteger('as_saturday')->comment("checked- 1, un-checked- 0 (come under `Community Access Support`)");
            $table->unsignedSmallInteger('as_sunday')->comment("checked- 1, un-checked- 0 (come under `Community Access Support`)");
            $table->unsignedSmallInteger('as_sleep_over')->comment("checked- 1, un-checked- 0 (come under `Community Access Support`)");
            $table->unsignedSmallInteger('as_active_night')->comment("checked- 1, un-checked- 0 (come under `Community Access Support`)");
            $table->unsignedSmallInteger('as_public_holiday')->comment("checked- 1, un-checked- 0 (come under `Community Access Support`)");
            
            $table->unsignedSmallInteger('archive')->comment("0- No, 1- Yes");
            $table->timestamp('created')->useCurrent();
            $table->unsignedInteger('created_by');
            $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->unsignedInteger('updated_by');
        });

        Schema::create('tbl_need_assessment_preferences_detail', function (Blueprint $table) {
            $table->increments('id'); 
            $table->unsignedInteger('preferences_id')->comment("tbl_need_assessment_preferences.id");
            $table->foreign('preferences_id')->references('id')->on('tbl_need_assessment_preferences')->onDelete('CASCADE');
            $table->unsignedSmallInteger('preferences_type')->comment("like- 1, dislike- 2, Support Worker Language-3, Support Worker Culture-4, Support Worker Intrests-5 ");
            $table->unsignedSmallInteger('type_id')->comment("tbl_references.id");           
            $table->unsignedSmallInteger('archive')->comment("0- No, 1- Yes");
            $table->timestamp('created')->useCurrent();
            $table->unsignedInteger('created_by');
            $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->unsignedInteger('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_need_assessment_preferences');
        Schema::dropIfExists('tbl_need_assessment_preferences_detail');
    }
}
