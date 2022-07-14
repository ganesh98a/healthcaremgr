<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblEmailRecipientAddEntityType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_sales_activity_email_recipient', function (Blueprint $table) {
            $table->unsignedInteger('entity_type')->nullable()->after('recipient')->comment('1 - contact / 4 - lead');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_sales_activity_email_recipient', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_sales_activity_email_recipient', 'entity_type')) {
                $table->dropColumn('entity_type');
            }
        });
    }
}
