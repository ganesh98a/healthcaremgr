<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOrganisationAddColumnAccountId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_organisation', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_organisation', 'accountId')) {
                $table->unsignedInteger('accountId')->comment('tbl_organisation.id')->nullable()->after("id");
            }

            if (!Schema::hasColumn('tbl_organisation', 'fax')) {
                $table->string('fax')->nullable()->after("website");
            }
            
            if (Schema::hasColumn('tbl_organisation', 'source_type')) {
                $table->unsignedInteger('source_type')->comment("0 - HCM/1 - org portal/ 3 - sales")->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_organisation', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_organisation', 'accountId')) {
                $table->dropColumn('accountId');
            }

            if (Schema::hasColumn('tbl_organisation', 'fax')) {
                $table->dropColumn('fax');
            }
        });
    }

}
