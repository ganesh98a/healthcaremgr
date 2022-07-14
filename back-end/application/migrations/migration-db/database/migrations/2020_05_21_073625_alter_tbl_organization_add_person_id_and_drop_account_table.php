<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOrganizationAddPersonIdAndDropAccountTable extends Migration {

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
        Schema::table('tbl_organisation', function (Blueprint $table) {
            $list = $this->listTableForeignKeys("tbl_organisation");

            Schema::dropIfExists('tbl_account');

            if (!Schema::hasColumn('tbl_organisation', 'org_code')) {
                $table->string('org_code', 255)->comment('is uniqe');
            }

            if (Schema::hasColumn('tbl_organisation', 'accountId')) {
                $table->dropColumn('accountId');
            }
            
            if (!Schema::hasColumn('tbl_organisation', 'person_id')) {
                $table->bigInteger('person_id')->unsigned()->comment('tbl_person.id')->nullable()->after("id");
            }

            if (!in_array("tbl_organisation_person_id_foreign", $list)) {
                $table->foreign('person_id')->references('id')->on('tbl_person');
            }
        });

        if (Schema::hasTable('tbl_organisation')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `tbl_organisation_before_insert_org_code`');
            DB::unprepared("CREATE TRIGGER `tbl_organisation_before_insert_org_code` BEFORE INSERT ON `tbl_organisation` FOR EACH ROW
                IF NEW.org_code IS NULL or NEW.org_code='' THEN
                SET NEW.org_code = (SELECT CONCAT('AT',(select LPAD(d.autoid_data,8,0)  from (select sum(Coalesce((SELECT id FROM tbl_organisation ORDER BY id DESC LIMIT 1),0)+ 1) as autoid_data) as d)));
                END IF;");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tbl_organisation', function (Blueprint $table) {
            $list = $this->listTableForeignKeys("tbl_organisation");

            if (Schema::hasColumn('tbl_organisation', 'org_code')) {
                $table->dropColumn('org_code')->comment('is uniqe');
            }

            if (in_array("tbl_organisation_person_id_foreign", $list)) {
                $table->dropForeign('tbl_organisation_person_id_foreign');
            }

            if (Schema::hasColumn('tbl_organisation', 'person_id')) {
                $table->dropColumn('person_id');
            }
        });
    }

}
