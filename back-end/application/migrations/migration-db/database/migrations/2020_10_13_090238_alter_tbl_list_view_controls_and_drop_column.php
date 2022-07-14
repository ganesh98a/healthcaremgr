<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblListViewControlsAndDropColumn extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    if (Schema::hasTable('tbl_list_view_controls')) {
      Schema::table('tbl_list_view_controls', function (Blueprint $table) {
        if (Schema::hasColumn('tbl_list_view_controls', 'pin_default')) {
          $table->dropColumn('pin_default');
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
    if (Schema::hasTable('tbl_list_view_controls') && Schema::hasColumn('tbl_list_view_controls', 'pin_default')) {
      Schema::table('tbl_list_view_controls', function (Blueprint $table) {
        if (Schema::hasColumn('tbl_list_view_controls', 'pin_default')) {
          $table->dropColumn('pin_default');
        }
      });
    }
  }
}
