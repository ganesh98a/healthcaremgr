<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblEmailTemplatesAddColumnFromAndSubject extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_email_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_email_templates', 'from')) {
                $table->string('from', 255)->after("name");
            }
			if (!Schema::hasColumn('tbl_email_templates', 'subject')) {
                $table->string('subject', 255)->after("from");
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
        Schema::table('tbl_email_templates', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_email_templates', 'from')) {
                $table->dropColumn('from');
            }
			if (Schema::hasColumn('tbl_email_templates', 'subject')) {
                $table->dropColumn('subject');
            }
        });
    }
}
