<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblSalesActivityAddNoteType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_sales_activity')) {
            Schema::table('tbl_sales_activity', function (Blueprint $table) {
                $table->smallInteger('note_type')->nullable()->default(0)->comment('"Profile" => "1", "Training" => "2", "Supervision" => "3", "OGA" => "4"');
                $table->smallInteger('confidential')->nullable()->default(0)->comment('confidential for application activity');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_sales_activity', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_sales_activity', 'note_type')) {
                $table->dropColumn('note_type');
            }
            if (Schema::hasColumn('tbl_sales_activity', 'confidential')) {
                $table->dropColumn('confidential');
            }
        });
    }
}
