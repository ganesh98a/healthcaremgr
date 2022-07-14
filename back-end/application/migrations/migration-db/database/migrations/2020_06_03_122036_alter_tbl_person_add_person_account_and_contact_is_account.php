<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblPersonAddPersonAccountAndContactIsAccount extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_person', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_person', 'person_account')) {
                $table->unsignedSmallInteger('person_account')->comment('0 - Not/ 1 - Yes')->nullable();
            }
            
            if (!Schema::hasColumn('tbl_person', 'contact_is_account')) {
                $table->unsignedSmallInteger('contact_is_account')->comment('0 - Not/ 1 - Yes')->nullable();
            }
			
			if (!Schema::hasColumn('tbl_person', 'owner')) {
                $table->unsignedInteger('owner')->comment('tbl_member.id')->nullable();
            }
        });
		
		Schema::table('tbl_organisation', function (Blueprint $table) {
			if (!Schema::hasColumn('tbl_organisation', 'owner')) {
                $table->unsignedInteger('owner')->comment('tbl_member.id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_person', function (Blueprint $table) {
             if (Schema::hasColumn('tbl_person', 'person_account')) {
                $table->dropColumn('person_account');
            }
            
            if (Schema::hasColumn('tbl_person', 'contact_is_account')) {
                $table->dropColumn('contact_is_account');
            }
			
			if (Schema::hasColumn('tbl_person', 'owner')) {
                $table->dropColumn('owner');
            }
        });
		
		Schema::table('tbl_organisation', function (Blueprint $table) {
			if (Schema::hasColumn('tbl_organisation', 'owner')) {
                $table->dropColumn('owner');
            }
        });
    }

}
