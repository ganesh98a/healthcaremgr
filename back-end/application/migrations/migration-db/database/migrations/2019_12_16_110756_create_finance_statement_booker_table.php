<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceStatementBookerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		 if (!Schema::hasTable('tbl_finance_statement_booker')) {
            Schema::create('tbl_finance_statement_booker', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('statementId')->comment('primary key tbl_finance_statement');
				$table->unsignedTinyInteger('booked_by')->comment('1 - site/2 - participant/3 - location(participant)/4- org/5 - sub-org/6 - reserve in quote');
                $table->unsignedInteger('bookerfor')->comment('primary key(id) of tbl_participant_booking_list , primary key(id) of tbl_organisation_all_contact,primary key(id) of tbl_organisation_all_contact');
                $table->dateTime('created');
                $table->unsignedTinyInteger('archive')->comment('0- Not/ 1 - Yes');
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
        Schema::dropIfExists('tbl_finance_statement_booker');
    }
}
