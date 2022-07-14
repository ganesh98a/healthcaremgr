<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblAdminBulkImport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_admin_bulk_import', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_admin_bulk_import', 'summary_text')) {
                $table->text('summary_text')->nullable()->after("error_text");
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
        Schema::table('tbl_admin_bulk_import', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_admin_bulk_import', 'summary_text')) {
                $table->dropColumn('summary_text');
            }
        });
    }
}
