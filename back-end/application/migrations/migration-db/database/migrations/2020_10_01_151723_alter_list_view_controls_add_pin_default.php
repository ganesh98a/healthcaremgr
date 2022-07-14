<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterListViewControlsAddPinDefault extends Migration
{
      /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_list_view_controls', function (Blueprint $table) {
            $table->boolean('pin_default')->default(0)->after('filter_operand')->comment('1 - pinned / 0 - unpinned');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_list_view_controls', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_list_view_controls', 'pin_default')) {
                $table->dropColumn('pin_default');
            }
        });
    }
}
