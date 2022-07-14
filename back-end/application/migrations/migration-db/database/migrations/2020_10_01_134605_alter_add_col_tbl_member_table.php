<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColTblMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_member', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_member', 'max_dis_to_travel')) {
                $table->decimal('max_dis_to_travel',9)->nullable()->after('dob');
            }
            if (!Schema::hasColumn('tbl_member', 'mem_experience')) {
                $table->decimal('mem_experience',9)->nullable()->after('hours_per_week');
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
            if (Schema::hasColumn('tbl_member', 'max_dis_to_travel')) {
                $table->dropColumn('max_dis_to_travel');
            }
            if (Schema::hasColumn('tbl_member', 'mem_experience')) {
                $table->dropColumn('mem_experience');
            }
        });
    }
}
