<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblParticipantsMasterAddMiddleNamePrevName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_participants_master', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_participants_master', 'previous_name')) {
                $table->string('previous_name',255)->nullable()->comment('previousname data of applicant')->after('name');
            }
            if (!Schema::hasColumn('tbl_participants_master', 'middlename')) {
                $table->string('middlename',255)->nullable()->comment('middlename data for contact')->after('contact_id');
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
        Schema::table('tbl_participants_master', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_participants_master', 'previous_name')) {
                $table->dropColumn('previous_name');
            }
            if (Schema::hasColumn('tbl_participants_master', 'middlename')) {
                $table->dropColumn('middlename');
            }
        });
    }
}
