<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFmsCaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_fms_case')) {
            Schema::create('tbl_fms_case', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('companyId');
                $table->timestamp('event_date')->default('0000-00-00 00:00:00');
                $table->unsignedInteger('shiftId')->default(0);
                $table->unsignedInteger('initiated_by')->index();
                $table->unsignedTinyInteger('initiated_type')->index()->comment('1- Member, 2- Participant, 3- ORG, 4- House, 5- member of public, 6- ONCALL (General), 7- ONCALL User/Admin');
                $table->timestamp('created')->default('0000-00-00 00:00:00');
                $table->unsignedTinyInteger('escalate_to_incident')->index()->default(0);
                $table->string('Initiator_first_name',150)->comment('in case of initiated_by public');
                $table->string('Initiator_last_name',150)->comment('in case of initiated_by public');
                $table->string('Initiator_email',150)->comment('in case of initiated_by public');
                $table->string('Initiator_phone',150)->comment('in case of initiated_by public');
                $table->unsignedTinyInteger('status')->index()->default(0)->comment('0- Ongoing, 1- Complete');
                $table->unsignedTinyInteger('fms_type')->default(0)->comment('0-case, 1- incident');
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
        Schema::dropIfExists('tbl_fms_case');
    }
}
