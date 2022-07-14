<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestimonialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tbl_testimonial')) {
            Schema::create('tbl_testimonial', function (Blueprint $table) {
                $table->increments('id');
                $table->string('full_name',100);
                $table->unsignedTinyInteger('module_type')->comment('1 - member/ 2 - participant');
                $table->string('title',200);
                $table->text('testimonial');
                $table->unsignedTinyInteger('status');
                $table->timestamp('created')->useCurrent();
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
        Schema::dropIfExists('tbl_testimonial');
    }
}
