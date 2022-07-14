<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblPersonAddColumnsPreferredPreviousNameDateOfBirtAndTitleRef extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_person', function(Blueprint $table) {
            if ( ! Schema::hasColumn('tbl_person', 'previous_name')) {
                $table->string('previous_name')->nullable();
            }

            if ( ! Schema::hasColumn('tbl_person', 'preferred_name')) {
                $table->string('preferred_name')->nullable();
            }

            if ( ! Schema::hasColumn('tbl_person', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable();
            }
            
            if ( ! Schema::hasColumn('tbl_person', 'title')) {
                // Usually the tbl_references.type has ID 3, which has equates to tbl_reference_data_type.key_name of 'title'
                $table->unsignedInteger('title')->nullable()->comment('tbl_references.id (usually type=3)'); 
                $table->foreign('title')->references('id')->on('tbl_references')->onDelete(DB::raw('SET NULL'));
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
        Schema::table('tbl_person', function(Blueprint $table) {
            if (Schema::hasColumn('tbl_person', 'title')) {
                $table->dropForeign(['title']); // In square brackets 
                $table->dropColumn('title');
            }

            if (Schema::hasColumn('tbl_person', 'preferred_name')) {
                $table->dropColumn('preferred_name');
            }

            if (Schema::hasColumn('tbl_person', 'previous_name')) {
                $table->dropColumn('previous_name');
            }

            if (Schema::hasColumn('tbl_person', 'date_of_birth')) {
                $table->dropColumn('date_of_birth');
            }
        });
    }
}
