<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOrganisationAddressAddArchive extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    
    public function listTableForeignKeys($table) {
        $conn = Schema::getConnection()->getDoctrineSchemaManager();

        return array_map(function($key) {
            return $key->getName();
        }, $conn->listTableForeignKeys($table));
    }
    
    public function up() {
        Schema::table('tbl_organisation_address', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_organisation_address', 'archive')) {
                $table->unsignedSmallInteger('archive')->comment('0 Not/1 - yes');
            }
        });

        Schema::table('tbl_organisation', function (Blueprint $table) {

            if (!Schema::hasColumn('tbl_organisation', 'updated')) {
                $table->dateTime('updated');
            }

            if (!Schema::hasColumn('tbl_organisation', 'updated_by')) {
                $table->integer('updated_by')->unsigned()->comment("tbl_member.id updated by")->nullable();
            }
        });

         Schema::table('tbl_organisation', function (Blueprint $table) {
            $list = $this->listTableForeignKeys("tbl_organisation");
            
            if (Schema::hasColumn('tbl_organisation', 'updated_by') && !in_array("tbl_organisation_updated_by_foreign", $list)) {
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onDelete('CASCADE');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_organisation_address', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_organisation_address', 'archive')) {
                $table->dropColumn('archive');
            }
        });

        Schema::table('tbl_organisation', function (Blueprint $table) {
            $list = $this->listTableForeignKeys("tbl_organisation");

            if (Schema::hasColumn('tbl_organisation', 'updated')) {
                $table->dropColumn('updated');
            }

            if (in_array("tbl_organisation_updated_by_foreign", $list)) {
                $table->dropForeign('tbl_organisation_updated_by_foreign');
            }

            if (!Schema::hasColumn('tbl_organisation', 'updated_by')) {
                $table->dropColumn('updated_by');
            }
        });
    }

}
