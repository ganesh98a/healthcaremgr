<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftLineItemAttachedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_shift_line_item_attached', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('shiftId')->nullable()->comment('tbl_shift auto incremant id');
            $table->unsignedInteger('line_item')->nullable()->comment('tbl_finance_line_item auto increment id');
            $table->decimal('quantity',10,2)->nullable()->comment('total_hours (apply time convert into in hr like 1 hr 45 min convert into 1.75 hr) this line item on shift duration');
            $table->double('cost', 10, 2);
            $table->string('xero_line_item_id',255)->nullable()->comment('when create invoice and data sent to xero after response get xero line item id updated');
            $table->dateTime('created')->default('0000-00-00 00:00:00');
            $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->smallInteger('archive')->default(0)->comment('0 -Not/1 - Archive');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_shift_line_item_attached');
    }
}
