
 <?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShiftGtTblAddOtypeRenameGTypeComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_shift_goal_tracking', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_shift_goal_tracking', 'outcome_type')) {
                $table->unsignedInteger('outcome_type')->nullable()->comment('1-Achieved,2-Partially Achieved')->after('goal_type');
            }
              if (Schema::hasColumn('tbl_shift_goal_tracking', 'goal_type')) {
                $table->unsignedInteger('goal_type')->comment('1-Not Attempted:Not relevant to this shift,
                2-Not Attempted:Customers Choice, 3-Verbal Prompt, 4-Physical Assistance, 5-Participant Proactivity')->change();
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
        Schema::table('tbl_shift_goal_tracking', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_shift_goal_tracking', 'outcome_type')) {
                $table->dropColumn('outcome_type');
            }
            if (Schema::hasColumn('tbl_shift_goal_tracking', 'goal_type')) {
                $table->unsignedInteger('goal_type')->comment('1-Not Attempted:Not relevant to this shift,
                2-Not Attempted:Customers Choice, 3-Verbal Prompt, 4-Physical Assistance, 5-Independent')->change();
            }
        });
    }
}
