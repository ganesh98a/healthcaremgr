<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterParticipantAddDefaultNullMedicareNumTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		 if (Schema::hasTable('tbl_participant')) {
            Schema::table('tbl_participant', function (Blueprint $table) {
                if(Schema::hasColumn('tbl_participant','medicare_num')){
                    $table->string('medicare_num',15)->nullable()->change();
                }
				
				if(Schema::hasColumn('tbl_participant','crn_num')){
                    $table->string('crn_num',15)->nullable()->change();
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
		 Schema::table('tbl_participant', function (Blueprint $table) {
            if(Schema::hasColumn('tbl_participant','medicare_num')){
                $table->dropColumn('medicare_num');
            }
            
            if(Schema::hasColumn('tbl_participant','crn_num')){
                $table->dropColumn('crn_num');
            }
          
        });
    }
}
