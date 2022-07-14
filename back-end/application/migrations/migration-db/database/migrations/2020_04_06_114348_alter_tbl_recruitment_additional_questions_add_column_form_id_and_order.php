<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblRecruitmentAdditionalQuestionsAddColumnFormIdAndOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
		if (Schema::hasTable('tbl_recruitment_additional_questions')) {
			Schema::table('tbl_recruitment_additional_questions', function (Blueprint $table) {
				if (!Schema::hasColumn('tbl_recruitment_additional_questions', 'form_id')) {
                     $table->unsignedBigInteger('form_id')->nullable()->comment('tbl_recruitment_form.id')->after("training_category");
                }
				
				if (!Schema::hasColumn('tbl_recruitment_additional_questions', 'display_order')) {
                    $table->unsignedBigInteger('display_order')->nullable()->comment('order to display')->after("form_id");
                }
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
        if (Schema::hasTable('tbl_recruitment_additional_questions')) {
			Schema::table('tbl_recruitment_additional_questions', function (Blueprint $table) {
				if (Schema::hasColumn('tbl_recruitment_additional_questions', 'form_id')) {
                     $table->dropColumn('form_id');
                }
				
				if (Schema::hasColumn('tbl_recruitment_additional_questions', 'display_order')) {
                     $table->dropColumn('display_order');
                }
			});
		}
    }
}
