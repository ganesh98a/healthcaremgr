<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnRenameEmailPasswordParticipantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_participant', function (Blueprint $table) {
            if (Schema::hasTable('tbl_participant')) {
              $table->renameColumn('gmail_account', 'invoice_email');
              $table->renameColumn('gmail_password', 'invoice_email_password');
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
          if (Schema::hasColumn('tbl_participant','gmail_account') && Schema::hasColumn('tbl_participant','gmail_password') ) {
            $table->renameColumn('invoice_email', 'gmail_account');
            $table->renameColumn('invoice_email_password', 'gmail_password');
          }
        });
    }
}
