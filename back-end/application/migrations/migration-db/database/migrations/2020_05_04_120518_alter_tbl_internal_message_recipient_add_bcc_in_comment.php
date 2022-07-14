<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblInternalMessageRecipientAddBccInComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if(Schema::hasTable('tbl_internal_message_recipient')){
			Schema::table('tbl_internal_message_recipient', function (Blueprint $table) {
				if(Schema::hasColumn('tbl_internal_message_recipient','cc')){
					$table->unsignedSmallInteger('cc')->default(0)->comment("0-not/1-CC/2-BCC")->change();
				}
			});
		}
		
		if(Schema::hasTable('tbl_external_message_recipient')){
			Schema::table('tbl_external_message_recipient', function (Blueprint $table) {
				if(!Schema::hasColumn('tbl_external_message_recipient','cc')){
					$table->unsignedSmallInteger('cc')->default(0)->comment("0-not/1-CC/2-BCC")->after("is_notify");
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
		if(Schema::hasTable('tbl_internal_message_recipient')){
			Schema::table('tbl_internal_message_recipient', function (Blueprint $table) {
				if(Schema::hasColumn('tbl_internal_message_recipient','cc')){
					$table->unsignedSmallInteger('cc')->default(0)->comment("0-not/1-CC")->change();
				}
			});
		}
		
        if(Schema::hasTable('tbl_external_message_recipient')){
			Schema::table('tbl_external_message_recipient', function (Blueprint $table) {
				if(Schema::hasColumn('tbl_external_message_recipient','cc')){
					$table->dropColumn('cc');
				}
			});
		}
    }
}
