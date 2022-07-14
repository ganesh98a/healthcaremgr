<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFmsFeedbackCategoryAddOtherField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('tbl_fms_feedback_category')) {
            Schema::table('tbl_fms_feedback_category', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_fms_feedback_category', 'other')) {
                    $table->text('other')->nullable()->after("categoryId")
                    ->comments('others feedback category');
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
        Schema::table('tbl_fms_feedback_category', function (Blueprint $table) {

            if (Schema::hasColumn('tbl_fms_feedback_category', 'other')) {

                $table->dropColumn('other');

            }
        });
    }
}
