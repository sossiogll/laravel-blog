<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Post;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->timestamps();
            $table->text("custom_fields")->nullable(true);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->after('author_id');
            $table->foreign('category_id')->references('id')->on('categories')->nullable(false)->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category');
    }
}
