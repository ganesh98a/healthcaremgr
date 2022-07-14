<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblPersonAddRemainingForeignAndTrigger extends Migration {

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

        Schema::table('tbl_person', function (Blueprint $table) {
            $list = $this->listTableForeignKeys("tbl_person");

            if (Schema::hasColumn('tbl_person', 'type') && !in_array("tbl_person_type_foreign", $list)) {
                $table->foreign('type')->references('id')->on('tbl_person_type');
            }

            if (Schema::hasColumn('tbl_person', 'person_source') && !in_array("tbl_person_person_source_foreign", $list)) {
                $table->foreign('person_source')->references('id')->on('tbl_lead_source_code');
            }

            if (Schema::hasColumn('tbl_person', 'created_by') && !in_array("tbl_person_created_by_foreign", $list)) {
                $table->foreign('created_by')->references('id')->on('tbl_member');
            }
        });

        if (Schema::hasTable('tbl_person')) {
            DB::unprepared('DROP TRIGGER  IF EXISTS `contact_before_insert_contact_number`');
            DB::unprepared("CREATE TRIGGER `contact_before_insert_contact_number` BEFORE INSERT ON `tbl_person` FOR EACH ROW
                IF NEW.contact_code IS NULL or NEW.contact_code='' THEN
                SET NEW.contact_code = (SELECT CONCAT('CT',(select LPAD(d.autoid_data,8,0)  from (select sum(Coalesce((SELECT id FROM tbl_person ORDER BY id DESC LIMIT 1),0)+ 1) as autoid_data) as d)));
                END IF;");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::unprepared('DROP TRIGGER  IF EXISTS `contact_before_insert_contact_number`');

        Schema::table('tbl_person', function (Blueprint $table) {
            $list = $this->listTableForeignKeys("tbl_person");

            if (Schema::hasColumn('tbl_person', 'type') && in_array("tbl_person_type_foreign", $list)) {
                $table->dropForeign('tbl_person_type_foreign');
            }

            if (Schema::hasColumn('tbl_person', 'person_source') && in_array("tbl_person_person_source_foreign", $list)) {
                $table->dropForeign('tbl_person_person_source_foreign');
            }

            if (Schema::hasColumn('tbl_person', 'created_by') && in_array("tbl_person_created_by_foreign", $list)) {
                $table->dropForeign('tbl_person_created_by_foreign');
            }
        });
    }

}
