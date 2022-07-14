<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldTblFmsCase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('tbl_fms_case', function(Blueprint $table)
        {
            if (!Schema::hasColumn('tbl_fms_case', 'assigned_to')) {
                $table->unsignedInteger('assigned_to')->nullable()->after('event_date')->comment('reference of tbl_member.id');
                $table->foreign('assigned_to')->references('id')->on('tbl_member')
                ->onUpdate('cascade')->onDelete('cascade')->comment('');
            }

            if (!Schema::hasColumn('tbl_fms_case', 'description')){
                $table->text('description')->nullable()->after('Initiator_phone');
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
