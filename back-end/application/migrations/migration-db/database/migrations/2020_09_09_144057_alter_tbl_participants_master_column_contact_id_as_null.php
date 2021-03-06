<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblParticipantsMasterColumnContactIdAsNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_participants_master', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_participants_master', 'contact_id')) {
                $table->unsignedInteger('contact_id')->nullable()->change();
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
            if (Schema::hasColumn('tbl_participants_master', 'contact_id')) {
                $table->unsignedInteger('contact_id')->nullable(false)->change();
            }
        });
    }
}
