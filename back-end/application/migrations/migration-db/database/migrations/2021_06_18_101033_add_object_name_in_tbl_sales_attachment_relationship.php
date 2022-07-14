<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddObjectNameInTblSalesAttachmentRelationship extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_sales_attachment_relationship', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_sales_attachment_relationship', 'object_name')) {
                $table->string('object_name',255)->nullable()->after('object_type');
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
        Schema::table('tbl_sales_attachment_relationship', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_sales_attachment_relationship', 'object_name')) {
                $table->dropColumn('object_name');
            }
        });
    }
}
