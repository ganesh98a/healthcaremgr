<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TblExternalMessageRecipientAddRecipinentTypeCommentAddApplicant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_external_message_recipient', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_external_message_recipient', 'recipinent_type')) {
                    $table->unsignedInteger('recipinent_type')->comment("1 - admin / 2 - participant / 3 - member / 4 - organisation/ 5 - applicant")->change();
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
        Schema::table('tbl_external_message_recipient', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_external_message_recipient', 'recipinent_type')) {
                    $table->unsignedInteger('recipinent_type')->comment("1 - admin / 2 - participant / 3 - member / 4 - organisation")->change();
            }
        });
    }
}
