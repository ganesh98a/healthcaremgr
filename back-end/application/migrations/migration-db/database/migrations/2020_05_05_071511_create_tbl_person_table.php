<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblPersonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Persons table is a generalized table for all 'contact-able' entities. 
        // In HCM contact-ables are applicant, admin, member, participant, etc...
        
        // Anyone that has email or phone can be put in this table
        if ( !  Schema::hasTable('tbl_person')) {
            Schema::create('tbl_person', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('firstname', 255);
                $table->string('lastname', 255);
                $table->unsignedSmallInteger('type')->default(0)->comment('1-applicant, 2-lead');
                $table->unsignedSmallInteger('archive')->default(0)->comment('no=0, yes=1');
                $table->unsignedSmallInteger('status')->default(1)->comment('inactive=0, active=1');

                $table->timestamp('created')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')); // let DB create updated timestamps instead of application code
            });
        }
        
        // Emails
        if ( !  Schema::hasTable('tbl_person_email')) {
            Schema::create('tbl_person_email', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('person_id')->comment('tbl_person.id');
                $table->string('email', 255);
                $table->unsignedSmallInteger('archive')->default(0)->comment('no=0, yes=1');
                $table->unsignedTinyInteger('primary_email')->comment('primary=1, secondary=2');

                $table->timestamp('created')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

                $table->foreign('person_id')->references('id')->on('tbl_person')->onDelete('CASCADE'); // destroy related row if a row in tbl_person.id is also destroyed
            });
        }
        
        // Phones
        if ( !  Schema::hasTable('tbl_person_phone')) {
            Schema::create('tbl_person_phone', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('person_id')->comment('tbl_person.id');
                $table->string('phone', 255);
                $table->unsignedSmallInteger('archive')->comment('no=0, yes=1');
                $table->unsignedTinyInteger('primary_phone')->comment('primary=1, secondary=2');
                
                $table->timestamp('created')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

                $table->foreign('person_id')->references('id')->on('tbl_person')->onDelete('CASCADE'); // destroy related row if a row in tbl_person.id is also destroyed
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
        Schema::dropIfExists('tbl_person_phone');
        Schema::dropIfExists('tbl_person_email');
        Schema::dropIfExists('tbl_person');
    }
}
