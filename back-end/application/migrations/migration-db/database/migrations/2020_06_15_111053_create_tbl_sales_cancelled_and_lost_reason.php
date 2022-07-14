<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblSalesCancelledAndLostReason extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('tbl_lead_unqualified_reason', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('lead_id')->comment('when type = 1 THEN, tbl_lead.id');
            $table->unsignedInteger('reason')->comment('tbl_references.id and with type tbl_reference_data_type.key_name = "unqualified_reason_lead"');
            $table->text('reason_note');
            $table->dateTime('created');
            $table->unsignedSmallInteger('archive')->comment("0-No/1-Yes");
        });

        $seeder = new ReferenceDataSeeder();
        $seeder->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('tbl_lead_unqualified_reason');
    }

}
