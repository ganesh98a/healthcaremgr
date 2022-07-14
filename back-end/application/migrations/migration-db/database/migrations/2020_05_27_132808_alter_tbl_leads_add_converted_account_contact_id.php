<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblLeadsAddConvertedAccountContactId extends Migration {

    public function listTableForeignKeys($table) {
        $conn = Schema::getConnection()->getDoctrineSchemaManager();

        return array_map(function($key) {
            return $key->getName();
        }, $conn->listTableForeignKeys($table));
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tbl_leads', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_leads', 'person_account')) {
                $table->unsignedSmallInteger('person_account')->defult(0)->comment("0-No/1-Yes")->after("updated_by");
            }

            if (!Schema::hasColumn('tbl_leads', 'contact_is_account')) {
                $table->unsignedSmallInteger('contact_is_account')->defult(0)->comment("0-No/1-Yes")->after("person_account");
            }

            if (!Schema::hasColumn('tbl_leads', 'converted_account_contact_id')) {
                $table->bigInteger('converted_account_contact_id')->unsigned()->comment("when person cheacked and contact account is not cheacked")->nullable()->after("converted_contact_id");
            }
        });

        Schema::table('tbl_leads', function (Blueprint $table) {
            $list = $this->listTableForeignKeys("tbl_leads");

            if (Schema::hasColumn('tbl_leads', 'converted_account_contact_id') && !in_array("tbl_leads_converted_account_contact_id_foreign", $list)) {
                $table->foreign('converted_account_contact_id')->references('id')->on('tbl_person');
            }
        });

        Schema::table('tbl_organisation', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_organisation', 'person_account')) {
                $table->dropColumn('person_account');
            }
        });

        Schema::table('tbl_person', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_person', 'contact_is_account')) {
                $table->dropColumn('contact_is_account');
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
            
            if (Schema::hasColumn('tbl_leads', 'person_account')) {
                $table->dropColumn('person_account');
            }

            if (Schema::hasColumn('tbl_leads', 'contact_is_account')) {
                $table->dropColumn('contact_is_account');
            }

            if (Schema::hasColumn('tbl_leads', 'converted_account_contact_id') && in_array("tbl_leads_converted_account_contact_id_foreign", $list)) {
                $table->dropForeign('tbl_leads_converted_account_contact_id_foreign');
            }

            if (Schema::hasColumn('tbl_leads', 'converted_account_contact_id')) {
                $table->dropColumn('converted_account_contact_id');
            }
        });
    }

}
