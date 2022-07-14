<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMemberPositionAwardAddSupportTypeColumnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('tbl_member_position_award') && !Schema::hasColumn('tbl_member_position_award', 'support_type')) {
                Schema::table('tbl_member_position_award', function (Blueprint $table) {
                $table->unsignedTinyInteger('support_type')->default(1)->comment('tbl_support_type primary key');
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
        if(Schema::hasTable('tbl_member_position_award') && Schema::hasColumn('tbl_member_position_award', 'support_type')) {
            Schema::table('tbl_member_position_award', function (Blueprint $table) {
                $table->dropColumn('support_type');
            });
        }
    }
}
