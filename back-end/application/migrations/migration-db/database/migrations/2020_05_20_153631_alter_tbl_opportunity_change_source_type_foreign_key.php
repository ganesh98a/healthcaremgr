<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOpportunityChangeSourceTypeForeignKey extends Migration {

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
        Schema::table('tbl_opportunity', function (Blueprint $table) {
            $list = $this->listTableForeignKeys("tbl_opportunity");

            if (Schema::hasColumn('tbl_opportunity', 'opportunity_source') && in_array("tbl_opportunity_opportunity_source_foreign", $list)) {
                $table->dropForeign('tbl_opportunity_opportunity_source_foreign');
            }

            if (Schema::hasColumn('tbl_opportunity', 'opportunity_source')) {
                $table->unsignedInteger('opportunity_source')->comment("tbl_references.id with tbl_reference_data_type.key_name = lead_source")->change();
            }
        });

         Schema::table('tbl_person', function (Blueprint $table) {
            $list = $this->listTableForeignKeys("tbl_person");
           
            if (Schema::hasColumn('tbl_person', 'person_source') && in_array("tbl_person_person_source_foreign", $list)) {
                $table->dropForeign('tbl_person_person_source_foreign');
                
                $table->unsignedInteger('person_source')->comment("tbl_references.id with tbl_reference_data_type.key_name = lead_source")->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_opportunity', function (Blueprint $table) {
            //
        });
    }

}
