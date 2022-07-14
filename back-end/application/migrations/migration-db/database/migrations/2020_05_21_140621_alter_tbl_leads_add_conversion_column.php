<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblLeadsAddConversionColumn extends Migration {

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
        Schema::table('tbl_leads', function (Blueprint $table) {
            $list = $this->listTableForeignKeys("tbl_leads");

            if (!Schema::hasColumn('tbl_leads', 'converted_opportunity_id')) {
                $table->bigInteger('converted_opportunity_id')->unsigned()->comment("tbl_opportunity.id")->nullable();
            }

            if (!Schema::hasColumn('tbl_leads', 'converted_organisation_id')) {
                $table->integer('converted_organisation_id')->unsigned()->comment("tbl_organisation.id")->nullable();
            }

            if (!Schema::hasColumn('tbl_leads', 'converted_contact_id')) {
                $table->bigInteger('converted_contact_id')->unsigned()->comment("tbl_person.id")->nullable();
            }

            if (!Schema::hasColumn('tbl_leads', 'converted_date')) {
                $table->dateTime('converted_date');
            }

            if (!Schema::hasColumn('tbl_leads', 'is_converted')) {
                $table->unsignedSmallInteger('is_converted')->comment("0 - Not/1 - Yes");
            }

            if (!Schema::hasColumn('tbl_leads', 'converted_by')) {
                $table->integer('converted_by')->unsigned()->comment("tbl_member.id admin")->nullable();
            }
        });


        Schema::table('tbl_leads', function (Blueprint $table) {
            $list = $this->listTableForeignKeys("tbl_leads");

            if (Schema::hasColumn('tbl_leads', 'converted_opportunity_id') && !in_array("tbl_leads_converted_opportunity_id_foreign", $list)) {
                $table->foreign('converted_opportunity_id')->references('id')->on('tbl_opportunity');
            }

            if (Schema::hasColumn('tbl_leads', 'converted_organisation_id') && !in_array("tbl_leads_converted_organisation_id_foreign", $list)) {
                $table->foreign('converted_organisation_id')->references('id')->on('tbl_organisation');
            }

            if (Schema::hasColumn('tbl_leads', 'converted_contact_id') && !in_array("tbl_leads_converted_contact_id_foreign", $list)) {
                $table->foreign('converted_contact_id')->references('id')->on('tbl_person');
            }

            if (Schema::hasColumn('tbl_leads', 'converted_by') && !in_array("tbl_leads_converted_by_foreign", $list)) {
                $table->foreign('converted_by')->references('id')->on('tbl_member');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_leads', function (Blueprint $table) {
            $list = $this->listTableForeignKeys("tbl_leads");


            if (Schema::hasColumn('tbl_leads', 'converted_opportunity_id') && in_array("tbl_leads_converted_opportunity_id_foreign", $list)) {
                $table->dropForeign('tbl_leads_converted_opportunity_id_foreign');
            }
            if (Schema::hasColumn('tbl_leads', 'converted_opportunity_id')) {
                $table->dropColumn('converted_opportunity_id');
            }



            if (Schema::hasColumn('tbl_leads', 'converted_organisation_id') && in_array("tbl_leads_converted_organisation_id_foreign", $list)) {
                $table->dropForeign('tbl_leads_converted_organisation_id_foreign');
            }
            if (Schema::hasColumn('tbl_leads', 'converted_organisation_id')) {
                $table->dropColumn('converted_organisation_id');
            }



            if (Schema::hasColumn('tbl_leads', 'converted_contact_id') && in_array("tbl_leads_converted_contact_id_foreign", $list)) {
                $table->dropForeign('tbl_leads_converted_contact_id_foreign');
            }
            if (Schema::hasColumn('tbl_leads', 'converted_contact_id') && in_array("tbl_leads_converted_contact_id_foreign", $list)) {
                $table->dropColumn('converted_contact_id');
            }



            if (Schema::hasColumn('tbl_leads', 'converted_date')) {
                $table->dropColumn('converted_date');
            }


            if (Schema::hasColumn('tbl_leads', 'is_converted')) {
                $table->dropColumn('is_converted');
            }


            if (Schema::hasColumn('tbl_leads', 'converted_by') && in_array("tbl_leads_converted_by_foreign", $list)) {
                $table->dropForeign('tbl_leads_converted_by_foreign');
            }
            if (Schema::hasColumn('tbl_leads', 'converted_by')) {
                $table->dropColumn('converted_by');
            }
        });
    }

}
