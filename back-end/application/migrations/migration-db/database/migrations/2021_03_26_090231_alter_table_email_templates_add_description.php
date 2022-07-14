<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableEmailTemplatesAddDescription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_email_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_email_templates', 'description')) {
                $table->string('description')->nullable()->after('name');
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
            if (Schema::hasColumn('tbl_email_templates', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
}
