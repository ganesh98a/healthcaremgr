<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_crm_logs')) {
            Schema::create('tbl_crm_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedSmallInteger('companyId')->nullable()->index('companyId');
                $table->unsignedInteger('userId')->nullable()->index('user_member_id');
                $table->unsignedInteger('module')->nullable()->index('module')->comment('1- Admin/ 2- Participant / 3 - Member/ 4 - Schedule / 5 - FSM / 6 - House / 7 - Organization / 8 - Imail / 9 - Recruitment / 10 - CRMAdmin');
                $table->unsignedInteger('sub_module')->nullable()->comment('if schedule (1 - Shift / 2 - Roster) , if Imail (1 - Externam mail/ 2 - Internal Mail) ');
                $table->text('title')->nullable();
                $table->text('description')->nullable();
                $table->timestamp('created')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->unsignedInteger('created_by')->nullable()->default(0);
                $table->unsignedTinyInteger('created_type')->comment('1 - admin / 2 - participant');

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
        Schema::dropIfExists('tbl_crm_logs');
    }
}
