<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddGenderColTblPersonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_person', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_person', 'gender')) {
                $table->unsignedBigInteger('gender')->comment('tbl_references.id with tbl_reference_data_type.key_name = gender')->nullable()->after("status");
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
        Schema::table('tbl_person', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_person', 'gender')) {
                $table->dropColumn('gender');
            }
        });
    }
}
