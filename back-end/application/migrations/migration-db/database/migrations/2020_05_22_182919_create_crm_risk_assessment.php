<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmRiskAssessment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create table with foreign key constraint

        Schema::create('tbl_crm_risk_assessment', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reference_id',200);
            $table->integer('status')->unsigned()->comment('(1 - Draft, 2- Final, 3 - Inactive)');
            $table->dateTime('created_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedInteger('created_by');
            $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop foreign key Constraint
        /*
           * Drop the key using the foreign key name or with [fieldname]
           * Table `tbl_crm_risk_assessment`
        */
        if (Schema::hasTable('tbl_crm_risk_assessment')) {
            Schema::table('tbl_crm_risk_assessment', function (Blueprint $table) {
                // Check the field is exist.
                if (Schema::hasColumn('tbl_crm_risk_assessment', 'created_by')) {
                    // Drop foreign key
                    $table->dropForeign(['created_by']);
                }
            });
        }
        Schema::dropIfExists('tbl_crm_risk_assessment');
    }
}
