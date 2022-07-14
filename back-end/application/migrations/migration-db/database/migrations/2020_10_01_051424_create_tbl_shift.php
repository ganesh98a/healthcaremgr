<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblShift extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        # rename the existing table to preserve it
        if (Schema::hasTable('tbl_shift')) {
            Schema::dropIfExists('tbl_shift_old');
            DB::statement("ALTER TABLE tbl_shift RENAME TO tbl_shift_old");
        }

        if (!Schema::hasTable('tbl_shift')) {
            Schema::create('tbl_shift', function (Blueprint $table) {
                $table->increments('id');
                $table->string('shift_no', 200);
                $table->unsignedInteger('account_type')->comment('1 = participant, 2 = org');
                $table->unsignedInteger('account_id')->comment('tbl_person.id or tbl_organization.id');
                $table->unsignedBigInteger('person_id')->nullable()->comment('tbl_person.id');
                $table->foreign('person_id')->references('id')->on('tbl_person')->onDelete(DB::raw('SET NULL'));
                $table->unsignedInteger('role_id')->comment('tbl_member_role.id');
                $table->foreign('role_id')->references('id')->on('tbl_member_role')->onUpdate('cascade')->onDelete('cascade');
                $table->unsignedInteger('owner_id')->nullable()->comment('tbl_member.id');
                $table->foreign('owner_id')->references('id')->on('tbl_member')->onDelete(DB::raw('SET NULL'));
                $table->unsignedInteger('status')->comment('1=Open, 2=Published, 3=Scheduled, 4=InProgress, 5=Completed, 6=Cancelled');
                $table->mediumtext('description')->nullable();
                $table->dateTime('scheduled_start_datetime');
                $table->dateTime('scheduled_end_datetime');
                $table->decimal('scheduled_unpaid_break', 10, 2)->nullable();
                $table->decimal('scheduled_paid_break', 10, 2)->nullable();
                $table->decimal('scheduled_travel', 10, 2)->nullable();
                $table->dateTime('actual_start_datetime')->nullable();
                $table->dateTime('actual_end_datetime')->nullable();
                $table->decimal('actual_unpaid_break', 10, 2)->nullable();
                $table->decimal('actual_paid_break', 10, 2)->nullable();
                $table->decimal('actual_travel', 10, 2)->nullable();
                $table->mediumtext('notes')->nullable();
                $table->unsignedInteger('archive')->default('0')->comment('0 = inactive, 1 = active');
                $table->dateTime('created')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
                $table->dateTime('updated')->nullable();
                $table->unsignedInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::table('tbl_shift', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift', 'owner_id')) {
                $table->dropForeign(['owner_id']);
            }
            if (Schema::hasColumn('tbl_shift', 'role_id')) {
                $table->dropForeign(['role_id']);
            }
            if (Schema::hasColumn('tbl_shift', 'person_id')) {
                $table->dropForeign(['person_id']);
            }
            if (Schema::hasColumn('tbl_shift', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('tbl_shift', 'created_by')) {
                $table->dropForeign(['created_by']);
            }
        });
        Schema::dropIfExists('tbl_shift');
    }
}
