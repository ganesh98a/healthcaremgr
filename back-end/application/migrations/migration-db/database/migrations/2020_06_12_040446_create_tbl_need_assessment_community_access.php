<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblNeedAssessmentCommunityAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_need_assessment_community_access')) {
            Schema::create('tbl_need_assessment_community_access', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('need_assessment_id')->comment("tbl_need_assessment.id");
                $table->foreign('need_assessment_id')->references('id')->on('tbl_need_assessment')->onDelete('CASCADE');
                $table->unsignedSmallInteger('not_applicable')->comment("1- Not applicable, 2- applicable");
                $table->unsignedSmallInteger('bowelcare')->comment("0- Not applicable, 1- with assistance, 2- with supervision, 3- independant");
                $table->unsignedSmallInteger('bladdercare')->comment("0- Not applicable, 1- with assistance, 2- with supervision, 3- independant");
                $table->unsignedSmallInteger('using_money')->comment("0- Not applicable, 1- with assistance, 2- with supervision, 3- independant");
                $table->unsignedSmallInteger('grocessary_shopping')->comment("0- Not applicable, 1- with assistance, 2- with supervision, 3- independant");
                $table->unsignedSmallInteger('paying_bills')->comment("0- Not applicable, 1- with assistance, 2- with supervision, 3- independant");
                $table->unsignedSmallInteger('swimming')->comment("0- Not applicable, 1- with assistance, 2- with supervision, 3- independant");
                $table->unsignedSmallInteger('road_safety')->comment("0- Not applicable, 1- with assistance, 2- with supervision, 3- independant");
                $table->unsignedSmallInteger('companion_cart')->comment("0- Not applicable, 1- Yes, 2- No");
                $table->unsignedSmallInteger('method_transport')->comment("0- Not applicable, 1- Public transport, 2- Support worker vehicle, 3- No paid transport");
                $table->unsignedSmallInteger('support_taxis')->comment("0- Not applicable, 1- Yes, 2- No");
                $table->string('support_taxis_desc',1000);
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
        Schema::dropIfExists('tbl_need_assessment_community_access');
    }
}
