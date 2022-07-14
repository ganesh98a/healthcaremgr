<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOrganisationAddCreatedDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_organisation', function (Blueprint $table) {
			    if(!Schema::hasColumn('tbl_organisation','created')){
                   
					 $table->dateTime('created')->default('0000-00-00 00:00:00');
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
        Schema::table('tbl_organisation', function (Blueprint $table) {
			 if(Schema::hasColumn('tbl_organisation','created')){
					 $table->dropColumn('created');
             }
        });
    }
}
