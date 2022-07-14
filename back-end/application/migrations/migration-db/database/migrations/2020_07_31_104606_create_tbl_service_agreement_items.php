<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblServiceAgreementItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_service_agreement_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_agreement_id')->comment("tbl_service_agreement.id");
            $table->foreign('service_agreement_id')->references('id')->on('tbl_service_agreement')->onDelete('CASCADE');
            $table->unsignedInteger('line_item_id')->comment('tbl_finance_line_item.id');
            $table->unsignedInteger('qty');
            $table->double('price', 10, 2);
            $table->double('amount', 10, 2)->comment('qty* price');
            $table->unsignedSmallInteger('archive')->comment("0-No/1-Yes");
            $table->timestamp('created')->useCurrent();
            $table->unsignedInteger('created_by');
            $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            $table->unsignedInteger('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_service_agreement_items')) {
            Schema::table('tbl_service_agreement_items', function (Blueprint $table) {
                // Check the field is exist.
                if (Schema::hasColumn('tbl_service_agreement_items', 'opportunity_id')) {
                    // Drop foreign key
                    $table->dropForeign(['opportunity_id']);
                }
            });
        }
        Schema::dropIfExists('tbl_service_agreement_items');
    }
}
