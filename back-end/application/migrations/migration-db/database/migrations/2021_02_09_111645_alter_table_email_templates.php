<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEmailTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_email_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_email_templates', 'folder')) {
                $table->enum('folder', ['public', 'private'])->default('public')->after('content');
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
            if (Schema::hasColumn('tbl_email_templates', 'folder')) {
                $table->dropColumn('folder');
            }
        });
    }
}
