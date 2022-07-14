<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableShiftNotesAddColumnNoteDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_shift_notes', 'note_date')) {
            Schema::table('tbl_shift_notes', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_shift_notes', 'note_date')) {
                    $table->date('note_date')->after('notes')->comment('note date specific by user')->default('0000-00-00')->nullable();
                }
            });

            if (Schema::hasColumn('tbl_shift_notes', 'note_date')) {
                DB::unprepared("update tbl_shift_notes set note_date= DATE(created) where note_date='0000-00-00'");
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_shift_notes', 'note_date')) {
            Schema::table('tbl_shift_notes', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_shift_notes', 'note_date')) {
                    $table->dropColumn('note_date');
                }
            });
        }
    }
}
