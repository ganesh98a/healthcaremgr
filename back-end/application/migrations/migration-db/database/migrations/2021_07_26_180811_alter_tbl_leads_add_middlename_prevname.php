<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTblLeadsAddMiddleNamePrevName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_leads', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_leads', 'previous_name')) {
                $table->string('previous_name',255)->nullable()->comment('previousname data of applicant')->after('lastname');
            }
            if (!Schema::hasColumn('tbl_leads', 'middlename')) {
                $table->string('middlename',255)->nullable()->comment('middlename data for contact')->after('firstname');
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
        Schema::table('tbl_leads', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_leads', 'previous_name')) {
                $table->dropColumn('previous_name');
            }
            if (Schema::hasColumn('tbl_leads', 'middlename')) {
                $table->dropColumn('middlename');
            }
        });
    }
}
