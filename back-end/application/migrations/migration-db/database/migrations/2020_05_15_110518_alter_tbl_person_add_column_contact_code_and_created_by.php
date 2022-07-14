<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblPersonAddColumnContactCodeAndCreatedBy extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::dropIfExists('tbl_contact');

        Schema::table('tbl_person', function (Blueprint $table) {

            if (!Schema::hasColumn('tbl_person', 'contact_code')) {
                $table->string('contact_code', 255)->after("id");
            }

            if (Schema::hasColumn('tbl_person', 'type')) {
                $table->bigInteger('type')->unsigned()->comment("tbl_person_type.id")->nullable()->change();
                //$table->foreign('type')->references('id')->on('tbl_person_type'); 
            }

            if (!Schema::hasColumn('tbl_person', 'person_source')) {
                $table->integer('person_source')->unsigned()->comment('tbl_lead_source_code.id')->nullable()->after("lastname");
                //$table->foreign('person_source')->references('id')->on('tbl_lead_source_code'); 
            }

            if (!Schema::hasColumn('tbl_person', 'created_by')) {
                $table->integer('created_by')->unsigned()->comment("tbl_member.id created by")->nullable()->after("type");
                //$table->foreign('created_by')->references('id')->on('tbl_member'); 
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //DB::unprepared('DROP TRIGGER  IF EXISTS `contact_before_insert_contact_number`');

        Schema::table('tbl_person', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_person', 'type')) {
                //DB::unprepared('ALTER TABLE `tbl_person` DROP FOREIGN KEY `tbl_person_type_foreign`');
                $table->unsignedInteger('type')->comment("1-applicant, 2-leads")->nullable()->change();
            }

            if (Schema::hasColumn('tbl_person', 'person_source')) {
                //DB::unprepared("ALTER TABLE `tbl_person` DROP FOREIGN KEY `tbl_person_person_source_foreign`");
                DB::unprepared('ALTER TABLE `tbl_person` DROP `person_source`;');
            }

            if (Schema::hasColumn('tbl_person', 'created_by')) {
                //DB::unprepared("ALTER TABLE `tbl_person` DROP FOREIGN KEY `tbl_person_created_by_foreign`");
                DB::unprepared('ALTER TABLE `tbl_person` DROP `created_by`;');
            }

            if (Schema::hasColumn('tbl_person', 'contact_code')) {
                $table->dropColumn('contact_code');
            }
        });
    }

}
