<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCreatedByUpdateByColumnCommentsInAllTable extends Migration
{

    public function __construct()
        {
            DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {       

        # Update the Existing Feedback id if exists
        $list_of_table = DB::select("SELECT TABLE_NAME AS hcm_db
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE()");

        if(!empty($list_of_table)) {
            foreach($list_of_table as $table_name) {
                $table = $table_name->hcm_db;
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'created_by')) {
                    Schema::table($table, function (Blueprint $table) {
                      $table->unsignedInteger('created_by')->comment('tbl_users.id')->change();
                    });
                  }
        
                  if (Schema::hasTable($table) && Schema::hasColumn($table, 'updated_by')) {
                    Schema::table($table, function (Blueprint $table) {
                      $table->unsignedInteger('updated_by')->comment('tbl_users.id')->change();
                    });
                  }

                  if (Schema::hasTable($table) && Schema::hasColumn($table, 'owner')) {
                    Schema::table($table, function (Blueprint $table) {
                      $table->unsignedInteger('owner')->comment('tbl_users.id')->change();
                    });
                  }

                  if (Schema::hasTable($table) && Schema::hasColumn($table, 'owner_id')) {
                    Schema::table($table, function (Blueprint $table) {
                      $table->unsignedInteger('owner_id')->comment('tbl_users.id')->change();
                    });
                  }

                  if (Schema::hasTable($table) && Schema::hasColumn($table, 'adminId')) {
                    Schema::table($table, function (Blueprint $table) {
                      $table->unsignedInteger('adminId')->comment('tbl_users.id')->change();
                    });
                  }
            }        
        }        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
