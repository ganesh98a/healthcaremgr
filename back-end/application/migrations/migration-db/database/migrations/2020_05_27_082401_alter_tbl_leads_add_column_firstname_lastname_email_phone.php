<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblLeadsAddColumnFirstnameLastnameEmailPhone extends Migration {

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

            if (!Schema::hasColumn('tbl_leads', 'firstname')) {
                $table->string('firstname', 255)->nullable()->after("lead_topic");
            }

            if (!Schema::hasColumn('tbl_leads', 'lastname')) {
                $table->string('lastname', 255)->nullable()->after("firstname");
            }

            if (!Schema::hasColumn('tbl_leads', 'email')) {
                $table->string('email', 255)->nullable()->after("lastname");
            }

            if (!Schema::hasColumn('tbl_leads', 'phone')) {
                $table->string('phone', 20)->nullable()->after("email");
            }

            if (Schema::hasColumn('tbl_leads', 'person_id') && in_array("tbl_leads_person_id_foreign", $list)) {
                $table->dropForeign('tbl_leads_person_id_foreign');
            }

            if (Schema::hasColumn('tbl_leads', 'person_id')) {
                $table->dropColumn('person_id');
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
            if (Schema::hasColumn('tbl_leads', 'issue_date')) {
                $table->dropColumn('firstname');
            }

            if (Schema::hasColumn('tbl_leads', 'lastname')) {
                $table->dropColumn('lastname');
            }

            if (Schema::hasColumn('tbl_leads', 'email')) {
                $table->dropColumn('email');
            }

            if (Schema::hasColumn('tbl_leads', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }

}
