<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterNotificationAddColumnSpecificAdminUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_notification')) {
            Schema::table('tbl_notification', function (Blueprint $table) {
                if(!Schema::hasColumn('tbl_notification','specific_admin_user')){
                    $table->unsignedInteger('specific_admin_user')->default(0)->nullable()->comment('0 mean all admin user notification send ,tbl_member auto increment id');
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
        if (Schema::hasTable('tbl_notification')) {
            Schema::table('tbl_notification', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_notification','specific_admin_user')){
                    $table->dropColumn('specific_admin_user');
                } 
            });

        }
    }
}
