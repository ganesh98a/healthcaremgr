<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblMemberSkill extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_member_skill', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('member_id')->comment('primary key of tbl_member');
            $table->unsignedInteger('skillId')->comment('primary key of tbl_participant_genral and type assistance');
            $table->string('other_title', 255);
            $table->dateTime('created')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_member_skill');
    }
}
