<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblFinancePayrateAsUpdateComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_finance_payrate')) {
            Schema::table('tbl_finance_payrate', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_finance_payrate','status')) {
                    $table->unsignedSmallInteger('status')->unsigned()->comment('1-Active, 2-Inactive')->change();
                     $table->renameColumn('archieve', 'archive');
                }
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
        if (Schema::hasTable('tbl_finance_payrate')) {
            Schema::table('tbl_finance_payrate', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_finance_payrate','status')) {
                    $table->unsignedSmallInteger('status')->unsigned()->comment('1- approved, 0- Default')->change();
                    $table->renameColumn('archive', 'archieve');
                }
            });
        }
    }
}
