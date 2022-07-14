<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProfileNoteInTblFmsFeedback extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_fms_feedback', function (Blueprint $table) {           
            if (!Schema::hasColumn('tbl_fms_feedback', 'is_profile_note')) {
                $table->unsignedTinyInteger('is_profile_note')->default(0)->comment('0 - No, 1 - Yes');
            }
            if (!Schema::hasColumn('tbl_fms_feedback', 'public_confidential_note')) {
                $table->unsignedTinyInteger('public_confidential_note')->default(0)->comment('1 - public_note, 2 - confidential_note');
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
        Schema::table('tbl_fms_feedback', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_fms_feedback', 'is_profile_note')) {
                $table->dropColumn('is_profile_note');
            }
            if (Schema::hasColumn('tbl_fms_feedback', 'public_confidential_note')) {
                $table->dropColumn('public_confidential_note');
            }           
        });
    }
}
