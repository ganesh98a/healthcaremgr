<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblOpportunity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_opportunity', function(Blueprint $table) {
            if (Schema::hasColumn('tbl_opportunity', 'opportunity_source')) {
                $table->unsignedInteger('opportunity_source')->comment('tbl_lead_source_code.id')->nullable()->change();
            }
            if (Schema::hasColumn('tbl_opportunity', 'owner')) {
                $table->unsignedInteger('owner')->comment('tbl_member.id admin id')->nullable()->change();
            }
            if (Schema::hasColumn('tbl_opportunity', 'account_person')) {
                $table->bigInteger('account_person')->unsigned()->comment("tbl_person.id")->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
