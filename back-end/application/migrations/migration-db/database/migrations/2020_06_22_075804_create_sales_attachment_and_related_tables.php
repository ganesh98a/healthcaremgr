<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesAttachmentAndRelatedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasTable('tbl_sales_attachment')) {
            Schema::create('tbl_sales_attachment', function(Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('file_name', 255)->comment('Name of the file that was uploaded, including the filename extension');
                $table->string('file_type', 255)->nullable()->comment('MIME type');
                $table->text('file_path')->nullable()->comment('The containing folder. Absolute server path to the file');
                $table->text('full_path')->nullable()->comment('Absolute server path, including the file name');
                $table->string('raw_name', 255)->nullable()->comment('File name, without the extension');
                $table->string('orig_name', 255)->nullable()->comment('Original file name. This is only useful if you use the encrypted name option.');
                $table->string('client_name', 255)->nullable()->comment('File name supplied by the client user agent, but possibly sanitized');
                $table->string('file_ext', 255)->nullable()->comment('Filename extension, period included');
                $table->unsignedBigInteger('file_size')->nullable()->comment('File size in kilobytes');
                $table->boolean('is_image')->nullable()->comment('Is gif, jpg, jpeg or png?. Use tbl_sales_attachment_meta to save image information');

                $table->boolean('archive')->default(0);

                // blamable cols
                $table->unsignedInteger('created_by')->nullable()->comment('tbl_member.id');
                $table->unsignedInteger('updated_by')->nullable()->comment('tbl_member.id');
                $table->foreign('created_by')->references('id')->on('tbl_member')->onDelete('SET NULL');
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onDelete('SET NULL');

                $table->timestamp('created')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->timestamp('updated')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            });
        }

        // Silo for attachment metadata or any not-so important data related to 
        // attachment (eg some EXIF meta, or geolocation, or some notes, etc)
        if ( ! Schema::hasTable('tbl_sales_attachment_meta')) {
            Schema::create('tbl_sales_attachment_meta', function(Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('sales_attachment_id')->comment('tbl_sales_attachment.id');
                $table->string('key', 255)->nullable()->comment('Meta key. Dont use spaces. Enumerate possible keys in application code.');
                $table->mediumText('value')->nullable()->comment('Meta value. For booleans, use 0,1. Can accept string or number. For complex data, pass JSON');

                // nullable timestamps, automatic
                $table->timestamp('created')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->timestamp('updated')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

                // blamable cols
                $table->unsignedInteger('created_by')->nullable()->comment('tbl_member.id');
                $table->unsignedInteger('updated_by')->nullable()->comment('tbl_member.id');
                $table->foreign('created_by')->references('id')->on('tbl_member')->onDelete('SET NULL');
                $table->foreign('updated_by')->references('id')->on('tbl_member')->onDelete('SET NULL');

                $table->foreign('sales_attachment_id')->references('id')->on('tbl_sales_attachment')->onDelete('CASCADE');
            });
        }


        if ( ! Schema::hasTable('tbl_sales_attachment_relationship_object_type')) {
            Schema::create('tbl_sales_attachment_relationship_object_type', function(Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255)->comment('Object name');
                $table->string('table', 255)->comment('Related table name. Table must have single PK (ie. id column)');
            });
        }


        // Assumming attachments can be reused by other objects (polymorphic many-to-many)
        // This is not req atm but this just in case
        if ( ! Schema::hasTable('tbl_sales_attachment_relationship')) {
            Schema::create('tbl_sales_attachment_relationship', function(Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('sales_attachment_id')->comment('tbl_sales_attachment.id');
                $table->unsignedBigInteger('object_id')->comment('Polymorphic id. Use with object_type. Dont use 0');
                $table->unsignedInteger('object_type')->comment('Dont use 0');
                
                $table->timestamp('created')->default(DB::raw('CURRENT_TIMESTAMP'));
                
                // Never use soft deletes on a junction table or on a table with strict UNIQUE checks 
                // or else the unique constraint will fail
                // Destroy the row instead of archiving

                $table->unique(['sales_attachment_id', 'object_id', 'object_type'], 'tbl_sales_attachment_relationship_unique_rel');

                $table->foreign('sales_attachment_id')->references('id')->on('tbl_sales_attachment')->onDelete('CASCADE');;
                $table->foreign('object_type')->references('id')->on('tbl_sales_attachment_relationship_object_type');
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
        Schema::dropIfExists('tbl_sales_attachment_relationship');
        Schema::dropIfExists('tbl_sales_attachment_relationship_object_type');
        Schema::dropIfExists('tbl_sales_attachment_meta');
        Schema::dropIfExists('tbl_sales_attachment');
    }
}
