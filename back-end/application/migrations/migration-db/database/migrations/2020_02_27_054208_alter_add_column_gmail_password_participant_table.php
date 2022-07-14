<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnGmailPasswordParticipantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_participant', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_participant','gmail_password') && !Schema::hasColumn('tbl_participant','gmail_account')) {
              $table->string('gmail_password', 30);
              $table->string('gmail_account', 255);
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
        Schema::table('tbl_participant', function (Blueprint $table) {
          if (Schema::hasColumn('tbl_participant','gmail_password') && Schema::hasColumn('tbl_participant','gmail_account')) {
            $table->dropColumn('gmail_password');
            $table->dropColumn('gmail_account');
          }
        });
    }
}
