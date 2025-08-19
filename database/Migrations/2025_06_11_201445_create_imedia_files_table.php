<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imedia__files', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            // Your fields...
            $table->boolean('is_folder')->default(false);
            $table->string('filename');
            $table->text('path')->nullable();
            $table->string('extension')->nullable();
            $table->string('mimetype')->nullable();
            $table->string('filesize')->nullable();
            $table->integer('folder_id')->nullable()->unsigned();
            $table->boolean('has_watermark')->default(false);
            $table->boolean("has_thumbnails")->default(false);
            $table->string('disk')->nullable();
            $table->string('visibility')->default('public');

            $table->foreign('folder_id')->references('id')->on('imedia__files')->onDelete('cascade');

            // Audit fields
            $table->timestamps();
            $table->auditStamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('imedia__files');
    }
};
