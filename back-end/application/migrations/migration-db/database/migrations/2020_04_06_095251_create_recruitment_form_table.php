<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruitmentFormTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('tbl_recruitment_form');
        Schema::create('tbl_recruitment_form', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->unsignedInteger('interview_type')->comment('tbl_recruitment_interview_type.id');
            $table->unsignedInteger('created_by')->comment('tbl_member.id');

            // equiv to $table->timestamps() but with diff column name
            $table->timestamp('date_created', 0)->nullable();
            $table->timestamp('date_updated', 0)->nullable();
        });

        // question belongs to form
        Schema::table('tbl_recruitment_additional_questions', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_additional_questions', 'form_id')) {
                $table->unsignedBigInteger('form_id')->nullable()->comment('tbl_recruitment_form.id')->after("training_category");
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
        if (Schema::hasColumn('tbl_recruitment_additional_questions', 'form_id')) {
            Schema::table('tbl_recruitment_additional_questions', function (Blueprint $table) {
                $table->dropColumn('form_id');
            });
        }

        Schema::dropIfExists('tbl_recruitment_form');
    }
}
