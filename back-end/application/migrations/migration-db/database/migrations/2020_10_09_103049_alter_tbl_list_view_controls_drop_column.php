<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblListViewControlsDropColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_list_view_controls', function (Blueprint $table) {
            if (Schema::hasTable('tbl_list_view_controls')) {
				Schema::table('tbl_list_view_controls', function (Blueprint $table) {
					if (Schema::hasColumn('tbl_list_view_controls','view_to_all_user')) {
						  $table->dropColumn('view_to_all_user');
                      }	
                    if (Schema::hasColumn('tbl_list_view_controls','view_to_one_user')) {
                        $table->renameColumn('view_to_one_user', 'user_view_by')->comment('1-current user, 2-all, 3-share');	
                    }			  
				});
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
        if (Schema::hasTable('tbl_list_view_controls') && Schema::hasColumn('tbl_list_view_controls','view_to_one_user')) {
            Schema::table('tbl_list_view_controls', function (Blueprint $table) {
                $table->renameColumn('view_to_one_user','user_view_by');
            });
          }
    }
}
