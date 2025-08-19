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
        Schema::create('imedia__file_translations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            // Your translatable fields
            $table->string('alt')->nullable();
            $table->string('keywords')->nullable();

            $table->integer('file_id')->unsigned();
            $table->string('locale')->index();
            $table->unique(['file_id', 'locale']);
            $table->foreign('file_id')->references('id')->on('imedia__files')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('imedia__file_translations', function (Blueprint $table) {
            $table->dropForeign(['file_id']);
        });
        Schema::dropIfExists('imedia__file_translations');
    }
};
