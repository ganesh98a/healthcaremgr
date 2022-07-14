<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCodeColumnToJobRequirementDocs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_recruitment_job_requirement_docs', function(Blueprint $table) {
            if (!Schema::hasColumn('tbl_recruitment_job_requirement_docs', 'code')) {
                $table->string('code', 255);
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
        Schema::table('tbl_recruitment_job_requirement_docs', function(Blueprint $table) {
            if (Schema::hasColumn('tbl_recruitment_job_requirement_docs', 'code')) {
                $table->dropColumn('code');
            }
        });
    }
}
