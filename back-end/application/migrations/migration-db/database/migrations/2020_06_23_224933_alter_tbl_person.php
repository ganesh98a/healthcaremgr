<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblPerson extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_person', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_person', 'religion')) {
                $table->string('religion',255)->after("date_of_birth");
            }

            if (!Schema::hasColumn('tbl_person', 'cultural_practices')) {
                $table->string('cultural_practices',255)->after("religion");
            }

            if (!Schema::hasColumn('tbl_person', 'aboriginal')) {
                $table->unsignedInteger('aboriginal')->default("0")->after("cultural_practices");
            }

            if (!Schema::hasColumn('tbl_person', 'communication_method')) {
                $table->unsignedInteger('communication_method')->default("0")->after("aboriginal");
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
            if (Schema::hasColumn('tbl_person', 'religion')) {
                $table->dropColumn('religion');
            }
            if (Schema::hasColumn('tbl_person', 'cultural_practices')) {
                $table->dropColumn('cultural_practices');
            }
            if (Schema::hasColumn('tbl_person', 'aboriginal')) {
                $table->dropColumn('aboriginal');
            }
            if (Schema::hasColumn('tbl_person', 'communication_method')) {
                $table->dropColumn('communication_method');
            }
        });
    }
}
