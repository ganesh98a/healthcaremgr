<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblOpportunityContact extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_opportunity_contact')) {
        Schema::create('tbl_opportunity_contact', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('opportunity_id');

            $table->unsignedInteger('contact_id');
            #$table->foreign('contact_id')->references('id')->on('tbl_person')->onDelete('CASCADE');

            $table->unsignedInteger('roll_id');
            $table->unsignedTinyInteger('is_primary')->comment("0-No/1-Yes");
            $table->timestamp('created')->useCurrent();           
            $table->unsignedInteger('created_by');
            #$table->foreign('created_by')->references('id')->on('tbl_member')->onDelete('CASCADE');

            $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->unsignedInteger('updated_by');
            #$table->foreign('updated_by')->references('id')->on('tbl_member')->onDelete('CASCADE');
            $table->unsignedSmallInteger('archive')->comment("0-No/1-Yes"); 
        });
    }
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_opportunity_contact');
    }
}
