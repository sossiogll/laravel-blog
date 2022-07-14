<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryPostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_post', function (Blueprint $table) {
            $table->id();
            $table->text('raw_custom_fields_values')->nullable(false);
            $table->timestamps();
            $table->unsignedBigInteger('category_id')->nullable(false);
            $table->unsignedBigInteger('post_id')->nullable(false);
            $table->foreign('category_id')->references('id')->on('categories')->nullable(false)->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('posts')->nullable(false)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('custom_fields_post_category');
    }
}
